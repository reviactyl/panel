<?php

namespace App\Http\Middleware;

use App\Events\Auth\FailedCaptcha;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerifyCaptcha
{
    /**
     * VerifyCaptcha constructor.
     */
    public function __construct(private Dispatcher $dispatcher, private Repository $config, private Client $client = new Client()) {}

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
            if (is_string($token) && trim($token) !== '') {
                $verified = $this->verifyToken($provider, $token, $request);
            }
        } elseif ($provider === 'turnstile') {
            $token = $request->input('cf-turnstile-response');
            if (is_string($token) && trim($token) !== '') {
                $verified = $this->verifyToken($provider, $token, $request);
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
     * Verify the token with the configured provider.
     */
    private function verifyToken(string $provider, string $token, Request $request): bool
    {
        $params = [
            'secret' => $this->config->get("captcha.$provider.secret_key"),
            'response' => $token,
        ];

        if ($provider === 'turnstile') {
            $params['remoteip'] = $request->ip();
        }

        try {
            $res = $this->client->post($this->config->get("captcha.$provider.domain"), [
                'connect_timeout' => 5,
                'timeout' => 10,
                'form_params' => $params,
            ]);

            if ($res->getStatusCode() !== 200) {
                return false;
            }

            $result = json_decode($res->getBody(), flags: JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException) {
            return false;
        }

        if (! $result instanceof \stdClass || ($result->success ?? null) !== true) {
            return false;
        }

        return ! $this->config->get('captcha.verify_domain') || ($result->hostname ?? null) === $request->getHost();
    }
}
