<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Lib\JsonResponse;
use App\Lib\AuthTokenClient;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthorizeController extends Controller
{
    protected AuthTokenClient $client;

    public function __construct(Client $client)
    {
        $client = Client::query()
            ->where('provider', 'users')
            ->latest()
            ->first();

        if (!$client) {
            throw new \RuntimeException('User Auth Client Not Found');
        }

        $this->client = new AuthTokenClient($client);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return JsonResponse::success([], "Alhamdulillah Registration successful");
    }

    public function login(Request $request)
    {
        // return $request;
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try {
            // Get the response from AuthTokenClient
            $response = $this->client->issueTokenForPassword(
                $request->email,
                $request->password,
            );

            // Return success response with the token data
            return JsonResponse::success($response);
        } catch (\Exception $exception) {
            // Return error response in case of exception
            return JsonResponse::error($exception);
        }
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);
        try {
            $token = $this->client->issueTokenForRefreshToken(
                refresh_token: $request->refresh_token,
            );
            return JsonResponse::success($token);
        } catch (\Exception $exception) {
            return JsonResponse::error($exception->getMessage());
        }
    }
}
