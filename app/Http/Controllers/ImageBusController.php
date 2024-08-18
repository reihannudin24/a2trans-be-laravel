<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bus;
use App\Models\ImageBus;
use App\Models\Category;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ImageBusController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->all();
        $busId = $data['bus_id'];
        $images = $data['images'];
        $token = $data['token'];
        $thumb = $data['thumb'];

        $tokenVerify = AuthHelper::validateUserToken($token);

        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 404);
        }

        try {
            if ($thumb) {
                DB::table('bus')->updateOrInsert(
                    ['id' => $busId],
                    ['thumb' => $images]
                );

                return response()->json([
                    'status' => 201,
                    'message' => 'Berhasil menambahkan bus image',
                    'data' => $images,
                    'redirect' => '/panel/list/vendor'
                ], 201);
            } else {
                DB::table('image_bus')->updateOrInsert(
                    ['bus_id' => $busId, 'image' => $images]
                );

                return response()->json([
                    'status' => 201,
                    'message' => 'Berhasil menambahkan bus image',
                    'data' => $images,
                    'redirect' => '/panel/list/vendor'
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $token = $data['token'];

        $tokenVerify = AuthHelper::validateUserToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 404);
        }

        try {
            ImageBus::destroy($id);

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menghapus image bus',
                'redirect' => '/panel/list/vendor'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        $token = $data['token'];

        $tokenVerify = AuthHelper::validateUserToken($token);
        if (!$tokenVerify['valid']) {
            return response()->json([
                'status' => 401,
                'message' => 'Token tidak valid',
                'error' => 'Token tidak valid',
                'redirect' => '/add/new/bus'
            ], 401);
        }

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Pengguna tidak valid',
                'error' => 'Pengguna tidak valid',
                'redirect' => '/add/new/bus'
            ], 404);
        }

        try {
            $query = DB::table('image_bus');
            if ($id) {
                $query->where('id', $id);
            }
            $imageBus = $query->get();

            if ($imageBus->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Gambar tidak ditemukan',
                    'error' => 'Gambar tidak ditemukan',
                    'redirect' => '/login'
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menampilkan image bus',
                'data' => $imageBus,
                'redirect' => '/panel/list/facilities'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'redirect' => '/panel'
            ], 500);
        }
    }
}
