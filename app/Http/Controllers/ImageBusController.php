<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Bus;
use App\Models\ImageBus;
use App\Models\Category;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ImageBusController extends Controller
{

    public function create(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'bus_id' => 'required',
            'images' => 'nullable|array', // Ensure 'images' is an array
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'bus_id.required' => 'Bus ID tidak boleh kosong',
            'images.array' => 'Field images harus berupa array',
            'images.*.image' => 'Setiap file harus berupa gambar',
            'images.*.mimes' => 'Gambar harus berupa file dengan format: jpeg, png, jpg, gif',
            'images.*.max' => 'Ukuran setiap gambar maksimal adalah 2MB',
        ]);

        // Check validation
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
            // Store each image in the image_bus table
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageFile = $image->store('upload/images/bus', 'public');
                    $imagePath = Storage::url($imageFile);

                    DB::table('image_buses')->insert([
                        'bus_id' => $validatedData['bus_id'],
                        'image' => $imagePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'status' => 201,
                'message' => 'Berhasil menambahkan bus image',
                'data' => [],
                'redirect' => '/panel/list/vendor'
            ], 201);
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
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'id.required' => 'ID gambar tidak boleh kosong',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'redirect' => '/add/new/bus'
            ], 400);
        }


        $validated = $validator->validate();
        $token = $request->bearerToken();

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
            $imageBus = ImageBus::findOrFail($validated['id']);

            if (Storage::disk('public')->exists($imageBus->image)) {
                Storage::disk('public')->delete($imageBus->image);
            }

            $imageBus->delete();

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

}
