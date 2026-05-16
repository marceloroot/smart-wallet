<?php

namespace App\Http\Controllers\Api;

use App\Application\Wallet\Register\RegisterUserCommand;
use App\Application\Wallet\Register\RegisterUserHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserHandler $registerUserHandler,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerUserHandler->handle(new RegisterUserCommand(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
        ));

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'wallet' => [
                'id' => $user->wallet->id,
                'balance_cents' => $user->wallet->balance_cents,
            ],
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();
        $user->load('wallet');
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'wallet' => [
                'id' => $user->wallet->id,
                'balance_cents' => $user->wallet->balance_cents,
            ],
            'token' => $token,
        ]);
    }

    public function logout(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
