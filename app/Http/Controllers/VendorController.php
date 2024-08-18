<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;

class VendorController extends Controller
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
            $vendor = new Vendor();
            $vendor->name = $name;
            $vendor->save();

            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan vendor',
                'redirect' => '/panel/list/vendor',
                'data' => $vendor
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
            $vendor = Vendor::find($id);
            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Vendor not found',
                    'redirect' => '/login'
                ], 404);
            }
            $vendor->name = $name;
            $vendor->user_id = $user->id;
            $vendor->save();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil memperbarui vendor',
                'redirect' => '/panel/list/vendor',
                'data' => $vendor
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui vendor',
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
            $vendor = Vendor::find($id);
            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Vendor not found',
                    'redirect' => '/panel'
                ], 404);
            }
            $vendor->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus vendor',
                'redirect' => '/panel/list/vendor'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus vendor',
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
            $query = Vendor::query();
            if ($id) {
                $query->where('id', $id);
            }
            $vendors = $query->get();

            return response()->json([
                'status' => 200,
                'message' => 'Vendor berhasil ditampilkan',
                'redirect' => '/panel/list/facilities',
                'data' => $vendors
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menampilkan vendor',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function checkToken($token)
    {
        // Implement your token verification logic here
        // For example, check if the token is valid or expired
        // Return an array like ['valid' => true/false]

        // Dummy implementation for illustration
        return ['valid' => true];
    }
}
