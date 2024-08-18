<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // TODO : ADD NEW USER === success
    public function addNewUser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string'
        ], [
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email telah terdaftar',
            'username.required' => 'Username tidak boleh kosong',
            'password.required' => 'Password tidak boleh kosong',
            'password.min' => 'Password harus minimal 6 karakter',
            'role.required' => 'Role tidak boleh kosong'
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/add/new/account'
            );
        }

        $validate = $validation->validate();

        DB::beginTransaction();

        $hashPassword = Hash::make($validate['password']);

        try {

            User::query()->insert([
                'email' => $validate['email'],
                'username' => $validate['username'],
                'password' => $hashPassword,
                'role' => $validate['role'],
                'remember_token' => null,
            ]);

            $user = User::query()->where('email',$validate['email'])->first();

            DB::commit();

            return ResponseHelper::successResponse(
                201,
                'Berhasil menambahkan pengguna',
                [
                    'user' => [
                        'email' => $user->email,
                        'id' => $user->id
                    ]
                ],
                '/panel'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                $e->getMessage(),
                '/add/new/account'
            );
        }
    }

    // TODO : LOGIN === success
    public function login(Request $request)
    {
        $validation = Validator::make($request->all() , [
            'email' => 'required|email',
            'password' => 'required'
        ] , [
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password tidak boleh kosong'
        ]);

        if ($validation->fails()){
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/login'
            );
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseHelper::errorResponse(
                401,
                'Email atau password tidak valid',
                '/login'
            );
        }

        DB::beginTransaction();

        try {
            // Generate token
            $token = $user->createToken('API Token for ' . $user->email)->plainTextToken;

            if (!$token) {
                DB::rollBack();
                return ResponseHelper::errorResponse(
                    500,
                    'Gagal menghasilkan token',
                    '/login'
                );
            }

            User::query()->where('id', $user->id)->update([
                'remember_token' => $token
            ]);

            Cookie::queue('auth_token', $token, 60 * 24, null, null, false, true);

            DB::commit();

            return ResponseHelper::successResponse(
                200,
                'Berhasil login',
                [
                    'user' => [
                        'email' => $user->email,
                        'token' => $token,
                        'id' => $user->id
                    ]
                ],
                '/panel'
            );
        } catch (\Exception $error) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                $error->getMessage(),
                '/login'
            );
        }
    }


    // TODO : LOGOUT === success
    public function logout(Request $request)
    {



        $validation = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid'
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/login'
            );
        }

        $token = $request->bearerToken();
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->remember_token !== $token) {
            return ResponseHelper::errorResponse(
                404,
                'Pengguna tidak ditemukan atau token tidak sesuai',
                '/login'
            );
        }

        DB::beginTransaction();

        try {
            User::query()->where('id', $user->id)->update(['remember_token' => null]);
            Cookie::queue(Cookie::forget('auth_token'));

            DB::commit();

            return ResponseHelper::successResponse(
                201,
                'Berhasil logout',
                null,
                '/login'
            );
        } catch (\Exception $error) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                $error->getMessage(),
                '/panel'
            );
        }
    }

}
