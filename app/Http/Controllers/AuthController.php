<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Handle user registration
    public function addNewUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $hashPassword = Hash::make($request->password);

            $user = User::create([
                'email' => $request->email,
                'username' => $request->username,
                'password' => $hashPassword,
                'role' => $request->role,
                'remember_token' => null,
            ]);

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan pengguna',
                'data' => [
                    'user' => [
                        'email' => $user->email,
                        'id' => $user->id
                    ]
                ],
                'redirect' => '/panel'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($e->getCode() === '23000') {
                return response()->json([
                    'status' => 401,
                    'message' => 'Email telah terdaftar',
                    'redirect' => '/add/new/account'
                ], 401);
            }
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    // Handle user login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Email atau password tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        DB::beginTransaction();

        try {
            $token = Auth::login($user);

            Cookie::queue('auth_token', $token, 60 * 24, null, null, false, true);

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Berhasil login',
                'data' => [
                    'user' => [
                        'email' => $user->email,
                        'token' => $token,
                        'id' => $user->id
                    ]
                ],
                'redirect' => '/panel'
            ], 201);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => $error->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    // Handle user logout
    public function logout(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $token = $request->bearerToken();
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->remember_token !== $token) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found or token mismatch',
                'redirect' => '/login'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $user->update(['remember_token' => null]);
            Cookie::queue(Cookie::forget('auth_token'));

            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Berhasil logout',
                'redirect' => '/login'
            ], 201);
        } catch (\Exception $error) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => $error->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }
}
