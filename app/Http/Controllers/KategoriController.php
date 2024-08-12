<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // Create a new category
    public function createKategori(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255',
            'status' => 'required|in:active,nonactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
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

            return response()->json(['message' => 'Kategori created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Kategori creation failed. Please try again.'], 500);
        }
    }

    // Get all categories
    public function getKategoris()
    {
        try {
            $kategoris = DB::table('kategoris')->get();
            return response()->json($kategoris, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve kategoris. Please try again.'], 500);
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
            return response()->json(['error' => $validator->errors()], 400);
        }

        DB::beginTransaction();

        try {
            $kategori = DB::table('kategoris')->where('id', $id)->first();

            if (!$kategori) {
                return response()->json(['error' => 'Kategori not found'], 404);
            }

            DB::table('kategoris')
                ->where('id', $id)
                ->update([
                    'nama_kategori' => $request->nama_kategori,
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json(['message' => 'Kategori updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Kategori update failed. Please try again.'], 500);
        }
    }

    // Delete a category
    public function deleteKategori($id)
    {
        DB::beginTransaction();

        try {
            $kategori = DB::table('kategoris')->where('id', $id)->first();

            if (!$kategori) {
                return response()->json(['error' => 'Kategori not found'], 404);
            }

            DB::table('kategoris')->where('id', $id)->delete();

            DB::commit();

            return response()->json(['message' => 'Kategori deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Kategori deletion failed. Please try again.'], 500);
        }
    }
}
