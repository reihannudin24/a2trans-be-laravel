<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;

class VendorController extends Controller
{
    // TODO: CREATE VENDOR === success
    public function create(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama vendor tidak boleh kosong',
            'name.string' => 'Nama vendor harus berupa string',
            'name.max' => 'Nama vendor maksimal 255 karakter',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(401, $validation->errors(), '/add/new/vendor');
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(401, 'Token tidak valid', '/add/new/vendor');
        }

        DB::beginTransaction();

        try {
            $vendor = new Vendor();
            $vendor->name = $validate['name'];
            $vendor->user_id = $user->id; // Associate vendor with the user
            $vendor->save();

            DB::commit();

            return ResponseHelper::successResponse(201, 'Berhasil menambahkan vendor', ['vendor' => $vendor], '/panel/list/vendor');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(500, 'Gagal menambahkan vendor', '/panel', $e->getMessage());
        }
    }

    // TODO: UPDATE VENDOR === success
    public function update(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required|exists:vendors,id',
            'name' => 'required|string|max:255',
        ], [
            'id.required' => 'ID vendor tidak boleh kosong',
            'id.exists' => 'Vendor tidak ditemukan',
            'name.required' => 'Nama vendor tidak boleh kosong',
            'name.string' => 'Nama vendor harus berupa string',
            'name.max' => 'Nama vendor maksimal 255 karakter',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(401, $validation->errors(), '/update/vendor');
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(401, 'Token tidak valid', '/update/vendor');
        }

        DB::beginTransaction();
        try {
            Vendor::query()->where('id' ,$validate['id'])->update([
                'name' => $validate['name']
            ]);
            $vendor = Vendor::query()->where('id', $validate['id'])->first();

            DB::commit();

            return ResponseHelper::successResponse(200, 'Berhasil memperbarui vendor', ['vendor' => $vendor], '/panel/list/vendor');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(500, 'Gagal memperbarui vendor', '/panel', $e->getMessage());
        }
    }

    // TODO: DELETE VENDOR === success
    public function delete(Request $request)
    {
        // Validate input
        $validation = Validator::make($request->all(), [
            'id' => 'required|exists:vendors,id',
        ], [
            'id.required' => 'ID vendor tidak boleh kosong',
            'id.exists' => 'Vendor tidak ditemukan',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::errorResponse(401, $validation->errors(), '/delete/vendor');
        }

        $validate = $validation->validate();
        $token = $request->bearerToken();

        // Verify token
        $user = User::where('remember_token', $token)->first();
        if (!$user) {
            return ResponseHelper::errorResponse(401, 'Token tidak valid', '/delete/vendor');
        }

        DB::beginTransaction();

        try {
            $vendor = Vendor::find($validate['id']);
            $vendor->delete();

            DB::commit();

            return ResponseHelper::successResponse(200, 'Berhasil menghapus vendor', null, '/panel/list/vendor');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::errorResponse(500, 'Gagal menghapus vendor', '/panel', $e->getMessage());
        }
    }

    // TODO: SHOW VENDOR === success
    public function show(Request $request)
    {
          $id = $request->query('id');

//        $token = $request->bearerToken();
//
//        // Verify token
//        $user = User::where('remember_token', $token)->first();
//        if (!$user) {
//            return ResponseHelper::errorResponse(401, 'Token tidak valid', '/login');
//        }

        try {
            $query = Vendor::query();
            if (!empty($id)) {
                $query->where('id', $id);
            }
            $vendors = $query->get();

            return ResponseHelper::successResponse(200, 'Vendor berhasil ditampilkan', ['vendors' => $vendors], '/panel/list/facilities');
        } catch (\Exception $e) {
            return ResponseHelper::errorResponse(500, 'Gagal menampilkan vendor', '/panel', $e->getMessage());
        }
    }
}
