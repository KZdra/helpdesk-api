<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Submit CSAT rating for a closed ticket
     */
    public function submitRating(Request $request, $ticket_id)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find ticket by ID or ticket_number
            $ticket = DB::table('tickets')
                ->where('id_ticket', $ticket_id)
                ->orWhere('ticket_number', $ticket_id)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            // Only ticket owner can rate
            if ($ticket->user_id !== $userId && Auth::user()->role_id !== 1) {
                return $this->errorResponse('Hanya pemilik tiket yang dapat memberikan penilaian kepuasan', 403);
            }

            // Only closed tickets can be rated
            if ($ticket->status !== 'closed') {
                return $this->errorResponse('Penilaian hanya dapat diberikan setelah tiket diselesaikan (closed)', 400);
            }

            // Check if already rated
            $existing = DB::table('ticket_ratings')->where('ticket_id', $ticket->id_ticket)->first();
            if ($existing) {
                return $this->errorResponse('Tiket ini sudah pernah diberikan penilaian', 400);
            }

            $ratingData = [
                'ticket_id' => $ticket->id_ticket,
                'user_id' => $userId,
                'rating' => $request->rating,
                'feedback' => $request->feedback,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $ratingId = DB::table('ticket_ratings')->insertGetId($ratingData);

            AuditLogService::log(
                'TICKET_RATED',
                'TicketRating',
                $ratingId,
                null,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'rating' => $request->rating,
                    'feedback' => $request->feedback
                ]
            );

            $result = DB::table('ticket_ratings')->where('id', $ratingId)->first();

            return $this->successResponse($result, 'Terima kasih, ulasan kepuasan Anda berhasil disimpan!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal menyimpan ulasan: ' . $e->getMessage());
        }
    }

    /**
     * Get rating for a specific ticket
     */
    public function getTicketRating($ticket_id)
    {
        try {
            $ticket = DB::table('tickets')
                ->where('id_ticket', $ticket_id)
                ->orWhere('ticket_number', $ticket_id)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            $rating = DB::table('ticket_ratings')
                ->join('users', 'ticket_ratings.user_id', '=', 'users.id')
                ->select('ticket_ratings.*', 'users.name as reviewer_name')
                ->where('ticket_ratings.ticket_id', $ticket->id_ticket)
                ->first();

            return $this->successResponse($rating);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil ulasan: ' . $e->getMessage());
        }
    }

    /**
     * Get CSAT Summary Report (Admin only)
     */
    public function getCsatReport(Request $request)
    {
        try {
            $query = DB::table('ticket_ratings')
                ->join('tickets', 'ticket_ratings.ticket_id', '=', 'tickets.id_ticket')
                ->leftJoin('users', 'ticket_ratings.user_id', '=', 'users.id')
                ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id');

            // Date Range Filter
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('ticket_ratings.created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('tickets.department_id', $request->department_id);
            }

            $totalReviews = (clone $query)->count();
            $averageRating = $totalReviews > 0 ? round((clone $query)->avg('rating'), 2) : 0;

            // Rating breakdown 1 to 5
            $distribution = [
                '1' => (clone $query)->where('rating', 1)->count(),
                '2' => (clone $query)->where('rating', 2)->count(),
                '3' => (clone $query)->where('rating', 3)->count(),
                '4' => (clone $query)->where('rating', 4)->count(),
                '5' => (clone $query)->where('rating', 5)->count(),
            ];

            // Satisfied percentage (ratings 4 and 5)
            $satisfiedCount = $distribution['4'] + $distribution['5'];
            $satisfactionRate = $totalReviews > 0 ? round(($satisfiedCount / $totalReviews) * 100, 1) : 0;

            // Recent reviews
            $recentReviews = (clone $query)
                ->select(
                    'ticket_ratings.id',
                    'ticket_ratings.rating',
                    'ticket_ratings.feedback',
                    'ticket_ratings.created_at',
                    'tickets.ticket_number',
                    'tickets.subject',
                    'users.name as client_name',
                    'departments.name as department_name'
                )
                ->orderBy('ticket_ratings.created_at', 'desc')
                ->limit(10)
                ->get();

            return $this->successResponse([
                'total_reviews' => $totalReviews,
                'average_score' => $averageRating,
                'satisfaction_rate_percent' => $satisfactionRate,
                'distribution' => $distribution,
                'recent_reviews' => $recentReviews,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil laporan kepuasan CSAT: ' . $e->getMessage());
        }
    }
}
