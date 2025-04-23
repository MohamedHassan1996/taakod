<?php

namespace App\Services\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Helpers\ApiResponse;
use App\Http\Resources\User\LoggedInUserResource;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{

    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'status' => UserStatus::ACTIVE,
            'type' => UserType::USER
        ]);

        // Generate a new token (DO NOT return it directly)
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'profile' => new LoggedInUserResource($user),
            'planDetails' => [
                'isSubscribed' => false,
                'features' => [],
                'startAt' => null,
                'endAt' => null
            ],
            'tokenDetails' => [
                'token' => $token,
                'expiresIn' => null
            ],
        ]);

    }

    public function login(array $data)
    {
        try {
            $user = User::where('email', $data['username'])->first();


            if (!$user || !Hash::check($data['password'], $user->password)) {
                return ApiResponse::error(__('auth.failed'), [], HttpStatusCode::UNAUTHORIZED);
            }

            if ($user->is_active == UserStatus::INACTIVE) {
                return ApiResponse::error(__('auth.inactive_account'), [], HttpStatusCode::UNAUTHORIZED);
            }

            // // Revoke old tokens (optional)
            $user->tokens()->delete();

            // Generate a new token (DO NOT return it directly)
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'profile' => new LoggedInUserResource($user),
                'planDetails' => [
                    'isSubscribed' => false,
                    'features' => [],
                    'startAt' => null,
                    'endAt' => null
                ],
                'tokenDetails' => [
                    'token' => $token,
                    'expiresIn' => null
                ],
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        $user = auth()->user();

        if ($user) {
            $user->tokens()->delete(); // Revoke all tokens
        }

        return ApiResponse::success([], __('auth.logged_out'));
    }
}
