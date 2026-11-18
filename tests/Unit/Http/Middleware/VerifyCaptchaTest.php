<?php

namespace Tests\Unit\Http\Middleware;

use App\Events\Auth\FailedCaptcha;
use App\Http\Middleware\VerifyCaptcha;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as ProviderRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyCaptchaTest extends TestCase
{
    private Repository $config;

    private Dispatcher $events;

    private array $failures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new Repository(['captcha' => [
            'verify_domain' => true,
            'recaptcha' => ['domain' => 'https://google.example/verify', 'secret_key' => 'recaptcha-secret'],
            'turnstile' => ['domain' => 'https://cloudflare.example/verify', 'secret_key' => 'turnstile-secret'],
        ]]);
        $this->failures = [];
        $this->events = new Dispatcher();
        $this->events->listen(FailedCaptcha::class, function (FailedCaptcha $event) {
            $this->failures[] = $event;
        });
    }

    public static function providers(): array
    {
        return [['recaptcha', 'g-recaptcha-response'], ['turnstile', 'cf-turnstile-response']];
    }

    public static function invalidTokens(): iterable
    {
        foreach (self::providers() as [$provider, $field]) {
            foreach ([null, '', '   ', [], ['token'], true, false, 0, 123, 1.5] as $index => $token) {
                yield "$provider token $index" => [$provider, $field, $token];
            }
        }
    }

    #[DataProvider('invalidTokens')]
    public function test_invalid_tokens_are_rejected_without_contacting_provider(string $provider, string $field, mixed $token): void
    {
        $handler = new MockHandler([new Response(200, [], '{"success":true,"hostname":"panel.example"}')]);
        $middleware = $this->middleware($provider, $handler);

        $this->assertRejected($middleware, $this->request([$field => $token]));
        $this->assertCount(1, $handler);
    }

    public static function verificationResponses(): iterable
    {
        $responses = [
            'matching host' => ['{"success":true,"hostname":"panel.example"}', true, true],
            'wrong host' => ['{"success":true,"hostname":"other.example"}', true, false],
            'missing host' => ['{"success":true}', true, false],
            'invalid host' => ['{"success":true,"hostname":["panel.example"]}', true, false],
            'wrong host allowed' => ['{"success":true,"hostname":"other.example"}', false, true],
            'missing host allowed' => ['{"success":true}', false, true],
            'matching host allowed' => ['{"success":true,"hostname":"panel.example"}', false, true],
            'provider failure' => ['{"success":false,"hostname":"panel.example"}', true, false],
            'provider failure unchecked host' => ['{"success":false}', false, false],
            'missing success' => ['{"hostname":"panel.example"}', false, false],
            'string success' => ['{"success":"false","hostname":"panel.example"}', true, false],
            'integer success' => ['{"success":1}', false, false],
            'null success' => ['{"success":null}', false, false],
            'invalid JSON' => ['{', false, false],
            'empty body' => ['', false, false],
            'null body' => ['null', false, false],
            'array body' => ['[]', false, false],
            'boolean body' => ['true', false, false],
            'string body' => ['"success"', false, false],
        ];

        foreach (self::providers() as [$provider, $field]) {
            foreach ($responses as $name => [$body, $verifyDomain, $accepted]) {
                yield "$provider $name" => [$provider, $field, $body, $verifyDomain, $accepted];
            }
        }
    }

    #[DataProvider('verificationResponses')]
    public function test_provider_response_and_domain_verification(string $provider, string $field, string $body, bool $verifyDomain, bool $accepted): void
    {
        $this->config->set('captcha.verify_domain', $verifyDomain);
        $middleware = $this->middleware($provider, new MockHandler([new Response(200, [], $body)]));
        $request = $this->request([$field => 'valid-token']);

        if (! $accepted) {
            $this->assertRejected($middleware, $request);

            return;
        }

        $this->assertSame('accepted', $middleware->handle($request, function (Request $passed) use ($request) {
            $this->assertSame($request, $passed);

            return 'accepted';
        }));
        $this->assertSame([], $this->failures);
    }

    #[DataProvider('providers')]
    public function test_provider_request_has_finite_timeouts_and_expected_fields(string $provider, string $field): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], '{"success":true,"hostname":"panel.example"}')]));
        $stack->push(Middleware::history($history));
        $middleware = $this->middleware($provider, $stack);

        $this->assertSame('accepted', $middleware->handle($this->request([$field => 'valid-token']), fn () => 'accepted'));
        $this->assertCount(1, $history);
        $transaction = $history[0];
        $this->assertSame($this->config->get("captcha.$provider.domain"), (string) $transaction['request']->getUri());
        $this->assertSame('POST', $transaction['request']->getMethod());
        $this->assertGreaterThan(0, $transaction['options']['connect_timeout']);
        $this->assertLessThanOrEqual(5, $transaction['options']['connect_timeout']);
        $this->assertGreaterThan(0, $transaction['options']['timeout']);
        $this->assertLessThanOrEqual(10, $transaction['options']['timeout']);
        parse_str((string) $transaction['request']->getBody(), $params);
        $expected = ['secret' => "$provider-secret", 'response' => 'valid-token'];
        if ($provider === 'turnstile') {
            $expected['remoteip'] = '192.0.2.10';
        }
        $this->assertSame($expected, $params);
    }

    public static function providerFailures(): iterable
    {
        foreach (self::providers() as [$provider, $field]) {
            foreach (['timeout', 'transport', 'http-error', 'unexpected-status'] as $failure) {
                yield "$provider $failure" => [$provider, $field, $failure];
            }
        }
    }

    #[DataProvider('providerFailures')]
    public function test_provider_failures_reject_normally(string $provider, string $field, string $failure): void
    {
        $request = new ProviderRequest('POST', $this->config->get("captcha.$provider.domain"));
        $response = match ($failure) {
            'timeout' => new ConnectException('Connection timed out', $request, null, ['errno' => 28]),
            'transport' => new RequestException('Transport failure', $request),
            'http-error' => new Response(503),
            'unexpected-status' => new Response(201, [], '{"success":true,"hostname":"panel.example"}'),
        };

        $this->assertRejected($this->middleware($provider, new MockHandler([$response])), $this->request([$field => 'token']));
    }

    #[DataProvider('providers')]
    public function test_other_provider_field_does_not_bypass_verification(string $provider, string $field): void
    {
        $other = $field === 'g-recaptcha-response' ? 'cf-turnstile-response' : 'g-recaptcha-response';
        $this->assertRejected($this->middleware($provider, new MockHandler()), $this->request([$other => 'token']));
    }

    public function test_disabled_captcha_bypasses_verification(): void
    {
        $middleware = $this->middleware('disable', new MockHandler());

        $this->assertSame('accepted', $middleware->handle($this->request(['g-recaptcha-response' => ['bad'], 'cf-turnstile-response' => ['bad']]), fn () => 'accepted'));
        $this->assertSame([], $this->failures);
    }

    private function middleware(string $provider, callable $handler): VerifyCaptcha
    {
        $this->config->set('captcha.provider', $provider);

        return new VerifyCaptcha($this->events, $this->config, new Client(['handler' => $handler]));
    }

    private function request(array $input): Request
    {
        return Request::create('https://panel.example/auth/login', 'POST', $input, server: ['REMOTE_ADDR' => '192.0.2.10']);
    }

    private function assertRejected(VerifyCaptcha $middleware, Request $request): void
    {
        try {
            $middleware->handle($request, function () {
                $this->fail('Rejected CAPTCHA must not continue the request.');
            });
            $this->fail('Expected a CAPTCHA rejection.');
        } catch (HttpException $exception) {
            $this->assertSame(400, $exception->getStatusCode());
            $this->assertSame('Failed to validate captcha.', $exception->getMessage());
        }

        $this->assertCount(1, $this->failures);
        $this->assertSame('192.0.2.10', $this->failures[0]->ip);
        $this->assertNull($this->failures[0]->domain);
    }
}
