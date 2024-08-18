<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CategoriesController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->input('token');
        $name = $request->input('name');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        // Create category
        try {
            $category = new Categories();
            $category->name = $name;
            $category->save();

            return response()->json([
                'status' => 201,
                'message' => 'Kategori berhasil ditambahkan',
                'redirect' => '/panel/list/vendor',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambahkan kategori',
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
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->storeAs('uploads/categories', time() . '-' . $file->getClientOriginalName(), 'public');
            $mediaPath = '/storage/' . $path;
        }

        // Update category
        try {
            $category = Categories::find($id);
            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Kategori tidak ditemukan',
                    'redirect' => '/panel'
                ], 404);
            }
            $category->name = $name;
            $category->icon = $mediaPath ?? $category->icon;
            $category->save();

            return response()->json([
                'status' => 200,
                'message' => 'Kategori berhasil diperbarui',
                'redirect' => '/panel/list/vendor',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui kategori',
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
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        // Delete category
        try {
            $category = Categories::find($id);
            if (!$category) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Kategori tidak ditemukan',
                    'redirect' => '/panel'
                ], 404);
            }
            $category->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Kategori berhasil dihapus',
                'redirect' => '/panel/list/vendor'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus kategori',
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
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => "Token tidak valid: {$token}",
                'redirect' => '/login'
            ], 401);
        }

        // Fetch category
        try {
            $query = Categories::query();
            if ($id) {
                $query->where('id', $id);
            }
            $categories = $query->get();

            return response()->json([
                'status' => 200,
                'message' => 'Kategori berhasil ditampilkan',
                'data' => $categories,
                'redirect' => '/panel/list/facilities'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menampilkan kategori',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
