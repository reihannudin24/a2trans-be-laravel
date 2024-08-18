<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Services\TokenService; // Assuming you have a service to handle token verification

class UserController extends Controller
{
    public function profile(Request $request)
    {

        $token = $request->bearerToken();

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(401, 'Token tidak valid', '/add/new/vendor');
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menampilkan pengguna',
            'data' => $user,
            'redirect' => '/profile'
        ], 200);
    }
}
