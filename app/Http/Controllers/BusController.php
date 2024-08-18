<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\TokenHelper;
use App\Helpers\UserHelper;
use App\Helpers\BusHelper;
use App\Helpers\ImageHelper;

class BusController extends Controller
{
    // Add new facilities to bus
    public function addFacilitiesToBus(Request $request)
    {
        $facilitiesData = $request->all();
        $facilities_id = $facilitiesData['facilities_id'];
        $bus_id = $facilitiesData['bus_id'];
        $token = $facilitiesData['token'];

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 404);
        }

        $bus = BusHelper::checkBus($bus_id);
        if (!$bus['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Gagal menampilkan bus',
                'error' => 'Bus tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        try {
            DB::insert('INSERT INTO pivot_bus_facilities (bus_id, facilities_id) VALUES (?, ?)', [$bus_id, $facilities_id]);
            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan fasilitas kedalam bus',
                'redirect' => '/add/new/bus'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambah fasilitas bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    // Delete facilities from bus
    public function deleteFacilitiesFromBus(Request $request)
    {
        $facilitiesData = $request->all();
        $id = $facilitiesData['id'];
        $token = $facilitiesData['token'];

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/login'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/login'
            ], 404);
        }

        $facilities = DB::select('SELECT * FROM pivot_bus_facilities WHERE id = ?', [$id]);
        if (empty($facilities)) {
            return response()->json([
                'status' => 401,
                'message' => 'Bus tidak memiliki fasilitas',
                'error' => 'Bus tidak memiliki fasilitas',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        try {
            DB::delete('DELETE FROM pivot_bus_facilities WHERE id = ?', [$id]);
            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus fasilitas dari bus',
                'redirect' => '/dashboard/list/bus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus fasilitas dari bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    // Create new bus
    public function create(Request $request)
    {
        $busData = $request->all();
        $name = $busData['name'];
        $description = $busData['description'];
        $seat = $busData['seat'];
        $type = $busData['type'];
        $categories_id = $busData['categories_id'];
        $merek_id = $busData['merek_id'];
        $token = $busData['token'];

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 401,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        if (!$name || !$description || !$seat || !$type || !$categories_id || !$merek_id) {
            return response()->json([
                'status' => 400,
                'message' => 'Kolom wajib tidak boleh kosong',
                'error' => 'Kolom wajib: name, description, seat, type, categories_id, merek_id tidak boleh kosong',
                'redirect' => '/add/new/bus'
            ], 400);
        }

        try {
            DB::insert('INSERT INTO bus (name, description, seat, type, categories_id, merek_id) VALUES (?, ?, ?, ?, ?, ?)', [$name, $description, $seat, $type, $categories_id, $merek_id]);
            return response()->json([
                'status' => 201,
                'message' => 'Bus berhasil ditambahkan',
                'redirect' => '/panel/list/bus'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambahkan bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel/add/new/bus'
            ], 500);
        }
    }

    // Update bus
    public function update(Request $request)
    {
        $busData = $request->all();
        $id = $busData['id'];
        $name = $busData['name'];
        $description = $busData['description'];
        $seat = $busData['seat'];
        $type = $busData['type'];
        $categories_id = $busData['categories_id'];
        $merek_id = $busData['merek_id'];
        $token = $busData['token'];

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/edit/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 401,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $bus = BusHelper::checkBus($id);
        if (!$bus['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Bus tidak valid',
                'error' => 'Bus tidak valid',
                'redirect' => '/edit/bus'
            ], 401);
        }

        if (!$name || !$merek_id || !$seat || !$type || !$categories_id) {
            return response()->json([
                'status' => 400,
                'message' => 'Kolom wajib tidak boleh kosong',
                'error' => 'Kolom wajib: name, seat, type, categories_id tidak boleh kosong',
                'redirect' => '/edit/bus'
            ], 400);
        }

        try {
            DB::update('UPDATE bus SET name = ?, description = ?, seat = ?, type = ?, categories_id = ?, merek_id = ? WHERE id = ?', [$name, $description, $seat, $type, $categories_id, $merek_id, $id]);
            return response()->json([
                'status' => 200,
                'message' => 'Bus berhasil diperbarui',
                'redirect' => '/panel/list/bus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal memperbarui bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel/edit/bus'
            ], 500);
        }
    }

    // Update bus images
    public function updateImages(Request $request)
    {
        $images = $request->file('images');
        $bus_id = $request->input('bus_id');
        $token = $request->input('token');

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 404);
        }

        $bus = BusHelper::checkBus($bus_id);
        if (!$bus['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Gagal menampilkan bus',
                'error' => 'Bus tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        try {
            foreach ($images as $image) {
                $imagePath = $image->store('public/images');
                DB::insert('INSERT INTO image_bus (bus_id, image_path) VALUES (?, ?)', [$bus_id, $imagePath]);
            }

            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan gambar bus',
                'redirect' => '/dashboard/list/bus'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menambahkan gambar bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    // Update bus thumbnail
    public function updateThumbnail(Request $request)
    {
        $thumbnail = $request->file('thumbnail');
        $bus_id = $request->input('bus_id');
        $token = $request->input('token');

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/edit/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/edit/bus'
            ], 404);
        }

        $bus = BusHelper::checkBus($bus_id);
        if (!$bus['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Gagal menampilkan bus',
                'error' => 'Bus tidak valid',
                'redirect' => '/edit/bus'
            ], 401);
        }

        if ($thumbnail) {
            $thumbnailPath = $thumbnail->store('public/images');
            try {
                DB::update('UPDATE bus SET thumb = ? WHERE id = ?', [$thumbnailPath, $bus_id]);
                return response()->json([
                    'status' => 200,
                    'message' => 'Berhasil memperbarui thumbnail bus',
                    'redirect' => '/dashboard/list/bus'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Gagal memperbarui thumbnail bus',
                    'error' => $e->getMessage(),
                    'redirect' => '/panel'
                ], 500);
            }
        }

        return response()->json([
            'status' => 400,
            'message' => 'Thumbnail tidak boleh kosong',
            'error' => 'Thumbnail tidak boleh kosong',
            'redirect' => '/edit/bus'
        ], 400);
    }

    // Delete bus
    public function delete(Request $request)
    {
        $bus_id = $request->input('bus_id');
        $token = $request->input('token');

        $tokenVerify = TokenHelper::checkToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/delete/bus'
            ], 401);
        }

        $user = DB::select('SELECT * FROM users WHERE remember_token = ?', [$token]);
        if (empty($user)) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/delete/bus'
            ], 404);
        }

        $bus = BusHelper::checkBus($bus_id);
        if (!$bus['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Gagal menampilkan bus',
                'error' => 'Bus tidak valid',
                'redirect' => '/delete/bus'
            ], 401);
        }

        try {
            DB::delete('DELETE FROM bus WHERE id = ?', [$bus_id]);
            return response()->json([
                'status' => 200,
                'message' => 'Bus berhasil dihapus',
                'redirect' => '/dashboard/list/bus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus bus',
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }
}
