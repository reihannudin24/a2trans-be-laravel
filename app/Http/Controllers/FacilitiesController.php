<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Facilities;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class FacilitiesController extends Controller
{
    public function create(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255'
        ], [
            'name.required' => 'Nama fasilitas tidak boleh kosong',
            'icon.required' => 'Ikon fasilitas tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'redirect' => '/add/new/bus'
            ], 400);
        }

        $validatedData = $validator->validated();

        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/add/new/bus'
            );
        }

        try {
            $facility = new Facilities();
            $facility->name = $validatedData['name'];
            $facility->icon = $validatedData['icon'];
            $facility->save();

            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan fasilitas',
                'redirect' => '/panel/list/vendor',
                'data' => $facility
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambahkan fasilitas',
                'redirect' => '/panel/add/new/bus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:facilities,id',
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255'
        ], [
            'id.required' => 'ID fasilitas tidak boleh kosong',
            'id.exists' => 'ID fasilitas tidak ditemukan',
            'name.required' => 'Nama fasilitas tidak boleh kosong',
            'icon.required' => 'Ikon fasilitas tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'redirect' => '/panel/add/new/bus'
            ], 400);
        }

        $validatedData = $validator->validated();

        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/add/new/bus'
            );
        }

        // Update facility
        try {
            $facility = Facilities::findOrFail($validatedData['id']);
            $facility->update([
                'name' => $validatedData['name'],
                'icon' => $validatedData['icon']
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil memperbarui fasilitas',
                'redirect' => '/panel/list/vendor',
                'data' => $facility
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui fasilitas',
                'redirect' => '/panel/add/new/bus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:facilities,id'
        ], [
            'id.required' => 'ID fasilitas tidak boleh kosong',
            'id.exists' => 'ID fasilitas tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'redirect' => '/panel'
            ], 400);
        }

        $validatedData = $validator->validated();
        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/add/new/bus'
            );
        }

        // Delete facility
        try {
            $facility = Facilities::findOrFail($validatedData['id']);
            $facility->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus fasilitas',
                'redirect' => '/panel/list/vendor'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus fasilitas',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $id = $request->query('id');

        try {
            $query = Facilities::query();
            if (!empty($id)) {
                $query->where('id', $id);
            }
            $facilities = $query->get();


            return ResponseHelper::successResponse(
                200,
                'fasilitas berhasil ditampilkan',
                [
                    'facilities' => $facilities
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
