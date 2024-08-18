<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Brand;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function create(Request $request)
    {
        // Define validation rules
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        // Check if the validation fails
        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 400);
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }

        try {
            $brand = new Brand();
            $brand->name = $validate['name'];
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
                'message' => 'Gagal menambahkan brand',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // Define validation rules
        $validation = Validator::make($request->all(), [
            'token' => 'required',
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        // Check if the validation fails
        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 400);
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }

        try {

            Brand::query()->where('id' ,$validate['id'])->update([
                'name' => $validate['name']
            ]);
            $brand = Brand::query()->where('id', $validate['id'])->first();
            if (!$brand) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Brand not found',
                    'redirect' => '/login'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil memperbarui brand',
                'redirect' => '/panel/list/brand',
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
        // Define validation rules
        $validation = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        // Check if the validation fails
        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation Error',
                'errors' => $validation->errors(),
            ], 400);
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();


        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
                'redirect' => '/login'
            ], 404);
        }


        try {
            $brand = Brand::find($validate['id']);
            if (!$brand) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Brand not found',
                    'redirect' => '/panel/list/brand'
                ], 404);
            }

            $brand->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus brand',
                'redirect' => '/panel/list/brand',
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
        $id = $request->query('id');

        try {

            $query = Brand::query();
            if (!empty($id)) {
                $query->where('id', $id);
            }
            $brand = $query->get();

            if (!$brand) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Brand not found',
                    'redirect' => '/panel/list/brand'
                ], 404);
            }

            return ResponseHelper::successResponse(
                200,
                'Berhasil menampilkan brand',
                [
                    'brand' => $brand
                ],
                '/panel/list/brand'
            );


        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menampilkan brand',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
