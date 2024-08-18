<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Services\TokenService; // Assuming you have a service to handle token verification

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $token = $request->header('Authorization'); // Assumes token is passed in the Authorization header

        // Verify the token
        $tokenService = new TokenService();
        $tokenVerify = $tokenService->checkToken($token);

        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid',
                'errors' => ['Token tidak valid'],
                'redirect' => '/login'
            ], 401);
        }

        // Fetch user by token
        $user = DB::table('users')->where('token_remember', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak terdaftar',
                'errors' => ['Email tidak terdaftar'],
                'redirect' => '/login'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menampilkan pengguna',
            'data' => $user,
            'redirect' => '/profile'
        ], 200);
    }
}
