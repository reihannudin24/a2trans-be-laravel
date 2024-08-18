<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facilities;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacilitiesController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->input('token');
        $name = $request->input('name');
        $icon = $request->input('icon');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        // Create facility
        try {
            $facility = new Facilities();
            $facility->name = $name;
            $facility->icon = $icon;
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
        $token = $request->input('token');
        $id = $request->input('id');
        $name = $request->input('name');
        $icon = $request->input('icon');

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        // Update facility
        try {
            $facility = Facilities::find($id);
            if (!$facility) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Fasilitas tidak ditemukan',
                    'redirect' => '/panel/add/new/bus'
                ], 404);
            }
            $facility->name = $name;
            $facility->icon = $icon;
            $facility->save();

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

        // Delete facility
        try {
            $facility = Facilities::find($id);
            if (!$facility) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Fasilitas tidak ditemukan',
                    'redirect' => '/panel'
                ], 404);
            }
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

        // Fetch facilities
        try {
            $query = Facilities::query();
            if ($id) {
                $query->where('id', $id);
            }
            $facilities = $query->get();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menampilkan fasilitas',
                'redirect' => '/panel/list/facilities',
                'data' => $facilities
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menampilkan fasilitas',
                'redirect' => '/panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
