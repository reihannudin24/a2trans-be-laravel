<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthHelper
{
    /**
     * Validate user token from the request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public static function validateUserToken(Request $request)
    {
        $userReqId = $request->user()->id;

        // Retrieve user details
        $user = User::find($userReqId);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak ditemukan',
                'redirect' => '/login'
            ], 404);
        }

        $token = $request->bearerToken(); // Get the token from the Authorization header

        if ($user->remember_token !== $token) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        return null; // Return null if validation passes
    }
}
