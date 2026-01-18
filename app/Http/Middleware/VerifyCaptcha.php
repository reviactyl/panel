<?php

namespace App\Http\Middleware;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\Auth\FailedCaptcha;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyCaptcha
{
    /**
     * VerifyCaptcha constructor.
     */
    public function __construct(private Dispatcher $dispatcher, private Repository $config)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $provider = $this->config->get('captcha.provider', 'disable');

        // If captcha is disabled, skip verification
        if ($provider === 'disable') {
            return $next($request);
        }

        $token = null;
        $verified = false;

        if ($provider === 'recaptcha') {
            $token = $request->input('g-recaptcha-response');
            if ($token) {
                $verified = $this->verifyRecaptcha($token, $request);
            }
        } elseif ($provider === 'turnstile') {
            $token = $request->input('cf-turnstile-response');
            if ($token) {
                $verified = $this->verifyTurnstile($token, $request);
            }
        }

        if ($verified) {
            return $next($request);
        }

        $this->dispatcher->dispatch(
            new FailedCaptcha(
                $request->ip(),
                null
            )
        );

        throw new HttpException(Response::HTTP_BAD_REQUEST, 'Failed to validate captcha.');
    }

    /**
     * Verify reCAPTCHA token.
     */
    private function verifyRecaptcha(string $token, Request $request): bool
    {
        $client = new Client();
        $res = $client->post($this->config->get('captcha.recaptcha.domain'), [
            'form_params' => [
                'secret' => $this->config->get('captcha.recaptcha.secret_key'),
                'response' => $token,
            ],
        ]);

        if ($res->getStatusCode() === 200) {
            $result = json_decode($res->getBody());

            if ($result->success && (!$this->config->get('captcha.verify_domain') || $this->isResponseVerified($result, $request))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify Cloudflare Turnstile token.
     */
    private function verifyTurnstile(string $token, Request $request): bool
    {
        $client = new Client();
        $res = $client->post($this->config->get('captcha.turnstile.domain'), [
            'form_params' => [
                'secret' => $this->config->get('captcha.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ],
        ]);

        if ($res->getStatusCode() === 200) {
            $result = json_decode($res->getBody());

            if ($result->success) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the response from the recaptcha servers was valid.
     */
    private function isResponseVerified(\stdClass $result, Request $request): bool
    {
        if (!$this->config->get('captcha.verify_domain')) {
            return false;
        }

        $url = parse_url($request->url());

        return $result->hostname === ($url['host'] ?? null);
    }
}
