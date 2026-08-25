<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SlaController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Get all SLA policies
     */
    public function getPolicies()
    {
        try {
            $policies = DB::table('sla_policies')
                ->leftJoin('priority', 'sla_policies.priority_id', '=', 'priority.id')
                ->select(
                    'sla_policies.*',
                    'priority.priority_name as linked_priority_name'
                )
                ->orderBy('sla_policies.id', 'asc')
                ->get();

            return $this->successResponse($policies);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil konfigurasi SLA: ' . $e->getMessage());
        }
    }

    /**
     * Get single SLA policy
     */
    public function getPolicy($id)
    {
        try {
            $policy = DB::table('sla_policies')->where('id', $id)->first();
            if (!$policy) {
                return $this->errorResponse('Kebijakan SLA tidak ditemukan', 404);
            }
            return $this->successResponse($policy);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil kebijakan SLA: ' . $e->getMessage());
        }
    }

    /**
     * Update SLA Policy (Admin only)
     */
    public function updatePolicy(Request $request, $id)
    {
        $policy = DB::table('sla_policies')->where('id', $id)->first();
        if (!$policy) {
            return $this->errorResponse('Kebijakan SLA tidak ditemukan', 404);
        }

        $validator = Validator::make($request->all(), [
            'response_time_minutes' => 'required|integer|min:1',
            'resolution_time_minutes' => 'required|integer|min:1',
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
                'response_time_minutes' => $request->response_time_minutes,
                'resolution_time_minutes' => $request->resolution_time_minutes,
                'is_active' => $request->input('is_active', $policy->is_active),
                'updated_at' => now(),
            ];

            DB::table('sla_policies')->where('id', $id)->update($updateData);

            AuditLogService::log(
                'SLA_POLICY_UPDATED',
                'SlaPolicy',
                $id,
                $policy,
                $updateData
            );

            $updated = DB::table('sla_policies')->where('id', $id)->first();
            return $this->successResponse($updated, 'Kebijakan SLA berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal memperbarui SLA: ' . $e->getMessage());
        }
    }

    /**
     * Get SLA Performance Summary Statistics
     */
    public function getSlaStats(Request $request)
    {
        try {
            $now = Carbon::now();
            $query = DB::table('tickets');

            // Optional Date Filter
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tickets.created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            $totalTickets = (clone $query)->count();
            $closedTickets = (clone $query)->where('status', 'closed')->count();
            $openOrProgressTickets = (clone $query)->whereIn('status', ['open', 'in_progress'])->count();

            // Breached tickets (either marked or current time exceeds due time)
            $breachedCount = (clone $query)
                ->where(function ($q) use ($now) {
                    $q->where('is_sla_breached', true)
                      ->orWhere(function ($sub) use ($now) {
                          $sub->whereIn('status', ['open', 'in_progress'])
                              ->whereNotNull('resolution_due_at')
                              ->where('resolution_due_at', '<', $now);
                      });
                })
                ->count();

            // Met tickets (closed within resolution due time)
            $metCount = (clone $query)
                ->where('status', 'closed')
                ->where('is_sla_breached', false)
                ->count();

            // Near breach count (open/in_progress tickets with less than 60 minutes remaining)
            $nearBreachCount = (clone $query)
                ->whereIn('status', ['open', 'in_progress'])
                ->where('is_sla_breached', false)
                ->whereNotNull('resolution_due_at')
                ->where('resolution_due_at', '>=', $now)
                ->where('resolution_due_at', '<=', $now->copy()->addMinutes(60))
                ->count();

            $slaComplianceRate = $closedTickets > 0 ? round(($metCount / $closedTickets) * 100, 1) : 100;

            return $this->successResponse([
                'total_tickets' => $totalTickets,
                'closed_tickets' => $closedTickets,
                'active_tickets' => $openOrProgressTickets,
                'sla_met' => $metCount,
                'sla_breached' => $breachedCount,
                'sla_near_breach' => $nearBreachCount,
                'compliance_rate_percent' => $slaComplianceRate,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil statistik SLA: ' . $e->getMessage());
        }
    }
}
