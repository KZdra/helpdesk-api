<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Get all departments (with search & pagination support)
     */
    public function getDepartments(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = DB::table('departments');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            $query->orderBy('name', 'asc');

            if ($request->input('paginate') === 'false' || $perPage === 'all') {
                $departments = $query->get();
            } else {
                $departments = $query->paginate((int) $perPage);
            }

            return $this->successResponse($departments);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil data departemen: ' . $e->getMessage());
        }
    }

    /**
     * Get active departments for select dropdowns
     */
    public function getActiveDepartments()
    {
        try {
            $departments = DB::table('departments')
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();

            return $this->successResponse($departments);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil departemen aktif: ' . $e->getMessage());
        }
    }

    /**
     * Get single department
     */
    public function getDepartment($id)
    {
        try {
            $department = DB::table('departments')->where('id', $id)->first();
            if (!$department) {
                return $this->errorResponse('Departemen tidak ditemukan', 404);
            }
            return $this->successResponse($department);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil detail departemen: ' . $e->getMessage());
        }
    }

    /**
     * Create department (Admin only)
     */
    public function createDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'is_active' => $request->input('is_active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $id = DB::table('departments')->insertGetId($data);

            AuditLogService::log(
                'DEPARTMENT_CREATED',
                'Department',
                $id,
                null,
                $data
            );

            $department = DB::table('departments')->where('id', $id)->first();
            return $this->successResponse($department, 'Departemen berhasil ditambahkan', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menambahkan departemen: ' . $e->getMessage());
        }
    }

    /**
     * Update department (Admin only)
     */
    public function updateDepartment(Request $request, $id)
    {
        $department = DB::table('departments')->where('id', $id)->first();
        if (!$department) {
            return $this->errorResponse('Departemen tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'is_active' => $request->input('is_active', $department->is_active),
                'updated_at' => now(),
            ];

            DB::table('departments')->where('id', $id)->update($updateData);

            AuditLogService::log(
                'DEPARTMENT_UPDATED',
                'Department',
                $id,
                $department,
                $updateData
            );

            $updated = DB::table('departments')->where('id', $id)->first();
            return $this->successResponse($updated, 'Departemen berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui departemen: ' . $e->getMessage());
        }
    }

    /**
     * Delete department (Admin only)
     */
    public function deleteDepartment($id)
    {
        try {
            $department = DB::table('departments')->where('id', $id)->first();
            if (!$department) {
                return $this->errorResponse('Departemen tidak ditemukan', 404);
            }

            DB::table('departments')->where('id', $id)->delete();

            AuditLogService::log(
                'DEPARTMENT_DELETED',
                'Department',
                $id,
                $department,
                null
            );

            return $this->successResponse(null, 'Departemen berhasil dihapus');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menghapus departemen: ' . $e->getMessage());
        }
    }
}
