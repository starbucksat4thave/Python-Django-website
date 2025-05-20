<?php

namespace App\Http\Controllers\api;

use App\Exceptions\Auth\NotAuthorizedException;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Login\UserLoginRequest;


class UserAuthController extends Controller
{
    public function login(UserLoginRequest $request)
    {
        try {
            Log::info('Starting user login.');
            $user = User::with(['roles', 'department'])->where('email', $request['email'])->first();

            if ($user->hasRole(['admin', 'super-admin'])){
                Log::warning('Unauthorized access attempt by admin or super-admin.', ['email' => $request['email']]);
                throw new NotAuthorizedException('Admins cannot log in here', 403);
            }

            if (!Hash::check($request['password'], $user->password)) {
                Log::warning('Invalid login attempt.', ['email' => $request['email']]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $tokenName = 'auth_token';
            $rememberMe = isset($request['remember_me']) && $request['remember_me'];


            $token = $user->createToken($tokenName)->plainTextToken;


            $expirationTime = $rememberMe ? now()->addDays(5) : now()->addMinutes(60);
            $user->tokens()->latest()->first()->forceFill([
                'expires_at' => $expirationTime,
            ])->save();

            Log::info('User logged in successfully.');

            return response()->json([
                'token' => $token,
                'user' => $user,
                'expires_at' => $expirationTime,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Login error.', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()?: 'An error occurred during login'], $e->getCode()?: 500);
        }
    }


    public function authUser(): JsonResponse
    {
        $user = Auth::user();
        $user->load('roles', 'department');

        if ($user->image) {
            $user->image_url = url('storage/' . $user->image);
        }

        return response()->json($user);
    }
}
