<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class CategoriesController extends Controller
{
    // TODO : CREATE CATEGORY === success
    public function create(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama kategori tidak boleh kosong',
            'name.string' => 'Nama kategori harus berupa string',
            'name.max' => 'Nama kategori maksimal 255 karakter',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/add/new/bus'
            );
        }

        $validate = $validation->validate();

        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Token tidak valid',
                '/add/new/bus'
            );
        }

        DB::beginTransaction();

        try {
            $category = new Categories();
            $category->name = $validate['name'];
            $category->save();

            DB::commit();

            return ResponseHelper::successResponse(
                201,
                'Kategori berhasil ditambahkan',
                [
                    'category' => $category
                ],
                '/panel/list/vendor'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal menambahkan kategori',
                '/panel',
                $e->getMessage()
            );
        }
    }

    // TODO : UPDATE CATEGORY === success
    public function update(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|file|mimes:jpg,jpeg,png'
        ], [
            'id.required' => 'ID kategori tidak boleh kosong',
            'id.exists' => 'Kategori tidak ditemukan',
            'name.required' => 'Nama kategori tidak boleh kosong',
            'name.string' => 'Nama kategori harus berupa string',
            'name.max' => 'Nama kategori maksimal 255 karakter',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/login'
            );
        }

        $validate = $validation->validate();

        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Token tidak valid',
                '/login'
            );
        }

        DB::beginTransaction();

        try {

            Categories::query()->where('id' ,$validate['id'])->update([
                'name' => $validate['name']
            ]);
            $category = Categories::query()->where('id', $validate['id'])->first();

//            // Handle file upload
//            if ($request->hasFile('image')) {
//                $file = $request->file('image');
//                $path = $file->storeAs('uploads/categories', time() . '-' . $file->getClientOriginalName(), 'public');
//                $mediaPath = '/storage/' . $path;
//                $category->icon = $mediaPath;
//            }

            // Update category
            DB::commit();

            return ResponseHelper::successResponse(
                200,
                'Kategori berhasil diperbarui',
                [
                    'category' => $category
                ],
                '/panel/list/vendor'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal memperbarui kategori',
                '/panel',
                $e->getMessage()
            );
        }
    }

    // TODO : DELETE CATEGORY === success
    public function delete(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required|exists:categories,id',
        ], [
            'id.required' => 'ID kategori tidak boleh kosong',
            'id.exists' => 'Kategori tidak ditemukan',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/login'
            );
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Token tidak valid',
                '/login'
            );
        }

        DB::beginTransaction();

        try {
            $category = Categories::find($validate['id']);
            $category->delete();

            DB::commit();

            return ResponseHelper::successResponse(
                200,
                'Kategori berhasil dihapus',
                null,
                '/panel/list/vendor'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal menghapus kategori',
                '/panel',
                $e->getMessage()
            );
        }
    }

    // TODO : SHOW CATEGORY === success
    public function show(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'nullable|exists:categories,id',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/login'
            );
        }

        $validated = $validation->validated();

        // Verify token
        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Token tidak valid',
                '/login'
            );
        }

        try {
            $query = Categories::query();
            if (!empty($validated['id'])) {
                $query->where('id', $validated['id']);
            }
            $categories = $query->get();

            return ResponseHelper::successResponse(
                200,
                'Kategori berhasil ditampilkan',
                [
                    'categories' => $categories
                ],
                '/panel/list/facilities'
            );
        } catch (\Exception $e) {
            return ResponseHelper::errorResponse(
                500,
                'Gagal menampilkan kategori',
                '/panel',
                $e->getMessage()
            );
        }
    }

}
