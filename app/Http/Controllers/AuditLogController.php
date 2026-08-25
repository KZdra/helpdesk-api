<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Get system audit trail logs (Admin only)
     */
    public function getLogs(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select(
                    'audit_logs.*',
                    'users.email as user_email'
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('audit_logs.action', 'like', "%{$search}%")
                      ->orWhere('audit_logs.entity_type', 'like', "%{$search}%")
                      ->orWhere('audit_logs.user_name', 'like', "%{$search}%")
                      ->orWhere('audit_logs.ip_address', 'like', "%{$search}%");
                });
            }

            if ($request->filled('action')) {
                $query->where('audit_logs.action', $request->action);
            }

            if ($request->filled('entity_type')) {
                $query->where('audit_logs.entity_type', $request->entity_type);
            }

            if ($request->filled('entity_id')) {
                $query->where('audit_logs.entity_id', $request->entity_id);
            }

            if ($request->filled('user_id')) {
                $query->where('audit_logs.user_id', $request->user_id);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('audit_logs.created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            $logs = $query->orderBy('audit_logs.created_at', 'desc')->paginate((int) $perPage);

            // Parse json values for cleaner responses
            $logs->getCollection()->transform(function ($item) {
                $item->old_values = $item->old_values ? json_decode($item->old_values) : null;
                $item->new_values = $item->new_values ? json_decode($item->new_values) : null;
                return $item;
            });

            return $this->successResponse($logs);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil catatan audit: ' . $e->getMessage());
        }
    }
}
