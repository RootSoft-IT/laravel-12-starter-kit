<?php

namespace App\Lib;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class AuthTokenClient
{
    protected string $client_id;

    protected string $client_secret;

    public function __construct(Client $client)
    {
        $this->client_id = $client->id;
        $this->client_secret = $client->secret;
    }

    public function issueTokenForPassword($username, $password, $scope = '')
    {
        return $this->issueToken([
            'grant_type' => 'password',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'username' => $username,
            'password' => $password,
        ]);
    }

    public function issueTokenForRefreshToken($refresh_token)
    {
        return $this->issueToken([
            'grant_type' => 'refresh_token',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $refresh_token,
        ]);
    }

    public function issueTokenForOTP($username, $otp, $otp_scope, $scope = '')
    {
        return $this->issueToken([
            'grant_type' => 'otp_grant',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'scope' => $scope,
            'username' => $username,
            'otp' => $otp,
            'otp_scope' => $otp_scope,
        ]);
    }

    private function issueToken(array $params)
    {
        $response = Http::withOptions([
            'verify' => false
        ])
            ->asForm()
            ->post(route('passport.token'), $params);

        if ($response->ok()) {
            return $response->json();
        }
        if (isset($response['message'])) {
            throw new \RuntimeException($response['message']);
        }
        $response->throw();
    }

    public function rateLimiter(Request $request, $key): object
    {
        return new class($request, $key) {
            protected Request $request;
            protected $key;

            public function __construct(Request $request, $key)
            {
                $this->request = $request;
                $this->key = $key;
            }
            public function throttleKey(): string
            {
                return Str::transliterate(Str::lower($this->request->input($this->key)) . '|' . $this->request->ip());
            }
            public function has(int $attempt = 3): bool
            {
                return RateLimiter::tooManyAttempts($this->throttleKey(), $attempt);
            }
            public function hit(int $second = 120): void
            {
                RateLimiter::hit($this->throttleKey(), $second);
            }
            public function availableIn(): int
            {
                return RateLimiter::availableIn(
                    $this->throttleKey()
                );
            }
            public function getMessage(): array
            {
                $seconds = $this->availableIn();
                $time = [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ];
                return [
                    'message' => trans('auth.throttle', $time),
                    'code' => Response::HTTP_TOO_MANY_REQUESTS,
                    'time' => $time
                ];
            }
        };
    }
}
