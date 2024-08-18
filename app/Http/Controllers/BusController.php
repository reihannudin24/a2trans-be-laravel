<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Bus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\TokenHelper;
use App\Helpers\UserHelper;
use App\Helpers\BusHelper;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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


    // TODO : UPDATE BUS IMAGES === success
    public function updateImages(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'bus_id' => 'required|exists:bus,id',
            'images.*' => 'required|file|mimes:jpg,jpeg,png',
            'token' => 'required|string',
        ], [
            'bus_id.required' => 'ID bus tidak boleh kosong',
            'bus_id.exists' => 'Bus tidak ditemukan',
            'images.*.required' => 'Gambar tidak boleh kosong',
            'images.*.file' => 'File tidak valid',
            'images.*.mimes' => 'Gambar harus berformat jpg, jpeg, atau png',
            'token.required' => 'Token tidak boleh kosong',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/add/new/bus'
            );
        }

        $validated = $validation->validated();

        $tokenVerify = TokenHelper::checkToken($validated['token']);
        if (!$tokenVerify['valid']) {
            return ResponseHelper::errorResponse(
                401,
                'Token tidak valid',
                '/add/new/bus'
            );
        }

        $user = User::where('remember_token', $validated['token'])->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                404,
                'Pengguna tidak valid',
                '/add/new/bus'
            );
        }

        $bus = Bus::find($validated['bus_id']);
        if (!$bus) {
            return ResponseHelper::errorResponse(
                401,
                'Bus tidak valid',
                '/add/new/bus'
            );
        }

        DB::beginTransaction();

        try {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('public/images');
                DB::table('image_bus')->insert([
                    'bus_id' => $validated['bus_id'],
                    'image_path' => $imagePath,
                ]);
            }

            DB::commit();

            return ResponseHelper::successResponse(
                201,
                'Gambar bus berhasil ditambahkan',
                null,
                '/dashboard/list/bus'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal menambahkan gambar bus',
                '/panel',
                $e->getMessage()
            );
        }
    }



    // TODO : CREATE BUS === success
    public function create(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'seat' => 'required|integer',
            'type' => 'required|string',
            'categories_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png',
        ], [
            'name.required' => 'Nama bus tidak boleh kosong',
            'description.required' => 'Deskripsi bus tidak boleh kosong',
            'seat.required' => 'Kapasitas kursi tidak boleh kosong',
            'type.required' => 'Tipe bus tidak boleh kosong',
            'categories_id.required' => 'ID kategori tidak boleh kosong',
            'brand_id.required' => 'ID merek tidak boleh kosong',
//            'vendor_id.required' => 'ID vendor tidak boleh kosong',
//            'thumbnail.required' => 'Thumbnail tidak boleh kosong',
            'thumbnail.file' => 'File tidak valid',
            'thumbnail.mimes' => 'Thumbnail harus berformat jpg, jpeg, atau png',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/add/new/bus'
            );
        }

        $validated = $validation->validate();

        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/add/new/bus'
            );
        }

        DB::beginTransaction();

        try {
            $thumbnailPath = $request->file('thumbnail')->store('upload', 'public');
            $thumbnailUrl = Storage::url($thumbnailPath);

            $bus = Bus::query()->insert([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'seat' => $validated['seat'],
                'thumb' =>  $thumbnailUrl,
                'type' => $validated['type'],
                'categories_id' => $validated['categories_id'],
                'brand_id' => $validated['brand_id'],
                'vendor_id' => $validated['vendor_id'],
            ]);

            DB::commit();

            return ResponseHelper::successResponse(
                201,
                'Bus berhasil ditambahkan',
                [
                    'bus' => $bus
                ],
                '/panel/list/bus'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal menambahkan bus',
                '/panel/add/new/bus',
                $e->getMessage()
            );
        }
    }

    // TODO : UPDATE BUS === success
    public function update(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'seat' => 'required|integer',
            'type' => 'required|string',
            'categories_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png',
//            'vendor_id' => 'required|exists:vendors,id',
//            'thumbnail' => 'required|file|mimes:jpg,jpeg,png',
        ], [
            'name.required' => 'Nama bus tidak boleh kosong',
            'description.required' => 'Deskripsi bus tidak boleh kosong',
            'seat.required' => 'Kapasitas kursi tidak boleh kosong',
            'type.required' => 'Tipe bus tidak boleh kosong',
            'categories_id.required' => 'ID kategori tidak boleh kosong',
            'brand_id.required' => 'ID merek tidak boleh kosong',
//            'vendor_id.required' => 'ID vendor tidak boleh kosong',
//            'thumbnail.required' => 'Thumbnail tidak boleh kosong',
            'thumbnail.file' => 'File tidak valid',
            'thumbnail.mimes' => 'Thumbnail harus berformat jpg, jpeg, atau png',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/edit/bus'
            );
        }

        $validated = $validation->validate();
        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/edit/bus'
            );
        }

        DB::beginTransaction();

        try {
            $bus = Bus::find($validated['id']);

            if (!$request->hasFile('thumbnail')) {
                return ResponseHelper::errorResponse(
                    401,
                    'Gambar thumbnail tidak ada',
                    '/edit/bus'
                );
            }

            if ($bus->thumb) {
                $oldThumbnailPath = str_replace('/storage/', '', $bus->thumb);
                Storage::disk('public')->delete($oldThumbnailPath);
            }

            // Store new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('upload', 'public');
            $thumbnailUrl = Storage::url($thumbnailPath);
            $bus->thumb = $thumbnailUrl;

            Bus::query()->where('id', $validated['id'])->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'seat' => $validated['seat'],
                'thumb' =>  $thumbnailUrl,
                'type' => $validated['type'],
                'categories_id' => $validated['categories_id'],
                'brand_id' => $validated['brand_id'],
                'vendor_id' => $validated['vendor_id'],
            ]);

            DB::commit();

            return ResponseHelper::successResponse(
                200,
                'Bus berhasil diperbarui',
                [
                    'bus' => $bus
                ],
                '/panel/list/bus'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal memperbarui bus',
                '/panel/edit/bus',
                $e->getMessage()
            );
        }
    }

    // TODO : DELETE BUS === success
    public function delete(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'id.required' => 'ID bus tidak boleh kosong',
            'id.exists' => 'Bus tidak ditemukan',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(
                401,
                $validation->errors(),
                '/delete/bus'
            );
        }

        $validated = $validation->validate();


        $token = $request->bearerToken();
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(
                401,
                'Pengguna tidak valid',
                '/delete/bus'
            );
        }

        DB::beginTransaction();

        try {
            $bus = Bus::find($validated['id']);

            if ($bus) {
                if ($bus->thumb) {
                    $thumbnailPath = str_replace('/storage/', '', $bus->thumb);
                    Storage::disk('public')->delete($thumbnailPath);
                }

                // Delete the bus record
                $bus->delete();
                DB::commit();

                return ResponseHelper::successResponse(
                    200,
                    'Bus berhasil dihapus',
                    null,
                    '/panel/list/bus'
                );
            } else {
                return ResponseHelper::errorResponse(
                    404,
                    'Bus tidak ditemukan',
                    '/panel/list/bus'
                );
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return ResponseHelper::errorResponse(
                500,
                'Gagal menghapus bus',
                '/panel/list/bus',
                $e->getMessage()
            );
        }
    }

//    public function delete(Request $request)
//    {
//        // Validate input
//        $validation = Validator::make($request->all(), [
//            'id' => 'required|exists:bus,id',
//            'token' => 'required|string',
//        ], [
//            'id.required' => 'ID bus tidak boleh kosong',
//            'id.exists' => 'Bus tidak ditemukan',
//            'token.required' => 'Token tidak boleh kosong',
//        ]);
//
//        if ($validation->fails()) {
//            return ResponseHelper::errorResponse(
//                401,
//                $validation->errors(),
//                '/delete/bus'
//            );
//        }
//
//        $validated = $validation->validated();
//
//        $tokenVerify = TokenHelper::checkToken($validated['token']);
//        if (!$tokenVerify['valid']) {
//            return ResponseHelper::errorResponse(
//                401,
//                'Token tidak valid',
//                '/delete/bus'
//            );
//        }
//
//        $user = User::where('remember_token', $validated['token'])->first();
//        if (!$user) {
//            return ResponseHelper::errorResponse(
//                401,
//                'Pengguna tidak valid',
//                '/delete/bus'
//            );
//        }
//
//        DB::beginTransaction();
//
//        try {
//            $bus = Bus::find($validated['id']);
//            if ($bus) {
//                $bus->delete();
//                DB::commit();
//
//                return ResponseHelper::successResponse(
//                    200,
//                    'Bus berhasil dihapus',
//                    null,
//                    '/panel/list/bus'
//                );
//            } else {
//                return ResponseHelper::errorResponse(
//                    404,
//                    'Bus tidak ditemukan',
//                    '/panel/list/bus'
//                );
//            }
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return ResponseHelper::errorResponse(
//                500,
//                'Gagal menghapus bus',
//                '/panel/list/bus',
//                $e->getMessage()
//            );
//        }
//    }

    // TODO : SHOW BUS === success
    public function show(Request $request)
    {
        $id = $request->query('id');

        try {
            $buses = Bus::when($id, function ($query, $id) {
                return $query->where('id', $id);
            })->get();

            return ResponseHelper::successResponse(
                200,
                'Bus berhasil ditampilkan',
                [
                    'buses' => $buses
                ],
                '/panel/list/bus'
            );
        } catch (\Exception $e) {
            return ResponseHelper::errorResponse(
                500,
                'Gagal menampilkan bus',
                '/panel/list/bus',
                $e->getMessage()
            );
        }
    }

}
