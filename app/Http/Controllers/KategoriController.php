<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class KategoriController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function createKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'status' => 'required|in:active,nonactive',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 400, $validator->errors());
        }

        DB::beginTransaction();

        try {
            DB::table('kategoris')->insert([
                'nama_kategori' => $request->nama_kategori,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return $this->successResponse(null, 'Kategori created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Kategori creation failed. Please try again.', 500);
        }
    }

    // Get all categories
    public function getKategoris()
    {
        try {
            $kategoris = DB::table('kategoris')->get();
            return $this->successResponse($kategoris);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve kategoris. Please try again.', 500);
        }
    }
    public function getActiveKategoris()
    {
        try {
            $kategoris = DB::table('kategoris')->where('status', 'active')->get();
            return $this->successResponse($kategoris);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve kategoris. Please try again.', 500);
        }
    }

    public function getKategori($id)
    {
        try {
            $kategori = DB::table('kategoris')->where('id', $id)->first();
            if (!$kategori) {
                return $this->errorResponse('Kategori not found', 404);
            }
            return $this->successResponse($kategori);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve kategori. Please try again.', 500);
        }
    }

    // Update a category
    public function updateKategori(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'status' => 'required|in:active,nonactive',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation errors', 400, $validator->errors());
        }

        DB::beginTransaction();

        try {
            $kategori = DB::table('kategoris')->where('id', $id)->first();

            if (!$kategori) {
                return $this->errorResponse('Kategori not found', 404);
            }

            DB::table('kategoris')
                ->where('id', $id)
                ->update([
                    'nama_kategori' => $request->nama_kategori,
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return $this->successResponse(null, 'Kategori updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Kategori update failed. Please try again.', 500);
        }
    }

    // Delete a category
    public function deleteKategori($id)
    {
        DB::beginTransaction();

        try {
            $kategori = DB::table('kategoris')->where('id', $id)->first();

            if (!$kategori) {
                return $this->errorResponse('Kategori not found', 404);
            }

            DB::table('kategoris')->where('id', $id)->delete();

            DB::commit();

            return $this->successResponse(null, 'Kategori deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Kategori deletion failed. Please try again.', 500);
        }
    }
}
