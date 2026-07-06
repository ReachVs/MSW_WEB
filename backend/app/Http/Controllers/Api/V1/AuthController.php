<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): UserResource
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        $role = Role::findOrCreate('customer');
        $user->assignRole($role);

        // For API registration, also issue a token
        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))
            ->additional(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials)) { // No session regeneration needed for API token login
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function updateProfile(Request $request): UserResource
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return new UserResource($user);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
