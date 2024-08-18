<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;

class BrandController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->input('token');
        $name = $request->input('name');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }

        $tokenVerify = $this->checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token not valid',
                'redirect' => '/login'
            ], 401);
        }

        try {
            $brand = new Brand();
            $brand->name = $name;
            $brand->save();

            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan brand',
                'redirect' => '/panel/list/brand',
                'data' => $brand
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambahkan vendor',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('id');
        $name = $request->input('name');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }

        // Token verification (you should implement this in a helper or service)
        $tokenVerify = $this->checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token not valid',
                'redirect' => '/login'
            ], 401);
        }

        // Update vendor
        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Brand not found',
                    'redirect' => '/login'
                ], 404);
            }
            $brand->name = $name;
            $brand->user_id = $user->id;
            $brand->save();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil memperbarui brand',
                'redirect' => '/panel/list/vendor',
                'data' => $brand
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui brand',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('id');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }

        // Token verification (you should implement this in a helper or service)
        $tokenVerify = $this->checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token not valid',
                'redirect' => '/login'
            ], 401);
        }

        // Delete vendor
        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Vendor not found',
                    'redirect' => '/panel'
                ], 404);
            }
            $brand->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus brand',
                'redirect' => '/panel/list/vendor'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus brand',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('id');

        // Verify token
        $tokenVerify = $this->checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        // Check if the user exists with the provided token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak ditemukan',
                'redirect' => '/login'
            ], 404);
        }

        // Fetch vendors
        try {
            $query = Brand::query();
            if ($id) {
                $query->where('id', $id);
            }
            $brand = $query->get();

            return response()->json([
                'status' => 200,
                'message' => 'Vendor berhasil ditampilkan',
                'redirect' => '/panel/list/brand',
                'data' => $brand
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menampilkan brand',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function checkToken($token)
    {
        return ['valid' => true];
    }
}
