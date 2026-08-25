<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function getTicket($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
                ->join('priority', 'tickets.priority_id', '=', 'priority.id')
                ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
                ->select(
                    'tickets.*',
                    'users.name as clientname',
                    'users.email as clientemail',
                    'kategoris.nama_kategori as kategori_name',
                    'priority.priority_name as priority',
                    'departments.name as department_name',
                    'departments.code as department_code'
                )
                ->where('tickets.ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            if ($ticket->attachment) {
                $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
            }

            // Include rating if available
            $rating = DB::table('ticket_ratings')->where('ticket_id', $ticket->id_ticket)->first();
            $ticket->rating = $rating;

            return $this->successResponse($ticket);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil tiket: ' . $e->getMessage());
        }
    }

    public function getTickets(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
                ->join('priority', 'tickets.priority_id', '=', 'priority.id')
                ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
                ->select(
                    'tickets.*',
                    'users.name as clientname',
                    'kategoris.nama_kategori as kategori_name',
                    'priority.priority_name as priority',
                    'departments.name as department_name',
                    'departments.code as department_code'
                );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tickets.ticket_number', 'like', "%{$search}%")
                      ->orWhere('tickets.subject', 'like', "%{$search}%")
                      ->orWhere('tickets.issue', 'like', "%{$search}%")
                      ->orWhere('users.name', 'like', "%{$search}%")
                      ->orWhere('kategoris.nama_kategori', 'like', "%{$search}%")
                      ->orWhere('priority.priority_name', 'like', "%{$search}%")
                      ->orWhere('departments.name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('tickets.status', $request->status);
            }

            if ($request->filled('ticket_type')) {
                $query->where('tickets.ticket_type', $request->ticket_type);
            }

            if ($request->filled('department_id')) {
                $query->where('tickets.department_id', $request->department_id);
            }

            if ($request->filled('priority_id')) {
                $query->where('tickets.priority_id', $request->priority_id);
            }

            if ($request->filled('kategori_id')) {
                $query->where('tickets.kategori_id', $request->kategori_id);
            }

            if ($request->filled('is_sla_breached')) {
                $query->where('tickets.is_sla_breached', filter_var($request->is_sla_breached, FILTER_VALIDATE_BOOLEAN));
            }

            $query->orderBy('tickets.created_at', 'desc');

            if ($request->input('paginate') === 'false' || $perPage === 'all') {
                $tickets = $query->get();
                foreach ($tickets as $ticket) {
                    if ($ticket->attachment) {
                        $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
                    }
                }
            } else {
                $tickets = $query->paginate((int) $perPage);
                $tickets->getCollection()->transform(function ($ticket) {
                    if ($ticket->attachment) {
                        $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
                    }
                    return $ticket;
                });
            }

            return $this->successResponse($tickets);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil daftar tiket: ' . $e->getMessage());
        }
    }

    public function getUserTickets(Request $request)
    {
        $userId = Auth::user()->id;
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('priority', 'tickets.priority_id', '=', 'priority.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
                ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
                ->select(
                    'tickets.*',
                    'users.name as clientname',
                    'kategoris.nama_kategori as kategori_name',
                    'priority.priority_name as priority',
                    'departments.name as department_name',
                    'departments.code as department_code'
                )
                ->where('tickets.user_id', $userId);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('tickets.ticket_number', 'like', "%{$search}%")
                      ->orWhere('tickets.subject', 'like', "%{$search}%")
                      ->orWhere('tickets.issue', 'like', "%{$search}%")
                      ->orWhere('kategoris.nama_kategori', 'like', "%{$search}%")
                      ->orWhere('priority.priority_name', 'like', "%{$search}%")
                      ->orWhere('departments.name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('tickets.status', $request->status);
            }

            if ($request->filled('ticket_type')) {
                $query->where('tickets.ticket_type', $request->ticket_type);
            }

            if ($request->filled('department_id')) {
                $query->where('tickets.department_id', $request->department_id);
            }

            if ($request->filled('priority_id')) {
                $query->where('tickets.priority_id', $request->priority_id);
            }

            if ($request->filled('kategori_id')) {
                $query->where('tickets.kategori_id', $request->kategori_id);
            }

            $query->orderBy('tickets.created_at', 'desc');

            if ($request->input('paginate') === 'false' || $perPage === 'all') {
                $tickets = $query->get();
                foreach ($tickets as $ticket) {
                    if ($ticket->attachment) {
                        $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
                    }
                }
            } else {
                $tickets = $query->paginate((int) $perPage);
                $tickets->getCollection()->transform(function ($ticket) {
                    if ($ticket->attachment) {
                        $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
                    }
                    return $ticket;
                });
            }

            return $this->successResponse($tickets);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function generateTicketNumber()
    {
        DB::beginTransaction();

        try {
            $date = date('ymd');
            $countTodayTickets = DB::table('numberings')
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->count();

            $nextSequenceNumber = $countTodayTickets + 1;
            $nextSequenceNumberFormatted = str_pad($nextSequenceNumber, 3, '0', STR_PAD_LEFT);
            $ticketNumber = 'TIX-' . $date . $nextSequenceNumberFormatted;

            $numberingId = DB::table('numberings')->insertGetId([
                'no_ticket' => $ticketNumber,
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'ticket_number' => $ticketNumber,
                'numbering_id' => $numberingId
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createTicket(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'subject' => 'required|string|max:255',
            'issue' => 'required|string',
            'priority_id' => 'required|exists:priority,id',
            'kategori_id' => 'required|exists:kategoris,id',
            'ticket_type' => 'nullable|in:incident,service_request,change_request',
            'department_id' => 'nullable|exists:departments,id',
            'attachment' => 'nullable|file|max:10240', // max 10MB
        ]);

        DB::beginTransaction();

        try {
            $ticketData = $this->generateTicketNumber();
            $originalFileName = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('attachments', $originalFileName, 'public');
            }

            // SLA Calculation based on priority
            $priority = DB::table('priority')->where('id', $request->priority_id)->first();
            $slaPolicy = null;
            if ($priority) {
                $slaPolicy = DB::table('sla_policies')
                    ->where('priority_name', 'like', $priority->priority_name)
                    ->orWhere('priority_id', $priority->id)
                    ->first();
            }

            $responseTimeMinutes = $slaPolicy ? $slaPolicy->response_time_minutes : 60;
            $resolutionTimeMinutes = $slaPolicy ? $slaPolicy->resolution_time_minutes : 240;

            $responseDueAt = Carbon::now()->addMinutes($responseTimeMinutes);
            $resolutionDueAt = Carbon::now()->addMinutes($resolutionTimeMinutes);

            $newTicket = [
                'user_id' => $userId,
                'ticket_number' => $ticketData['ticket_number'],
                'numbering_id' => $ticketData['numbering_id'],
                'priority_id' => $request->priority_id,
                'status' => 'open',
                'ticket_type' => $request->input('ticket_type', 'incident'),
                'department_id' => $request->department_id,
                'kategori_id' => $request->kategori_id,
                'subject' => $request->subject,
                'issue' => $request->issue,
                'attachment' => $originalFileName,
                'response_due_at' => $responseDueAt,
                'resolution_due_at' => $resolutionDueAt,
                'is_sla_breached' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $ticketId = DB::table('tickets')->insertGetId($newTicket);

            AuditLogService::log(
                'TICKET_CREATED',
                'Ticket',
                $ticketId,
                null,
                [
                    'ticket_number' => $ticketData['ticket_number'],
                    'subject' => $request->subject,
                    'priority_id' => $request->priority_id,
                    'ticket_type' => $request->input('ticket_type', 'incident'),
                    'response_due_at' => $responseDueAt,
                    'resolution_due_at' => $resolutionDueAt,
                ]
            );

            DB::commit();

            return $this->successResponse([
                'ticket_id' => $ticketId,
                'ticket_number' => $ticketData['ticket_number'],
                'response_due_at' => $responseDueAt,
                'resolution_due_at' => $resolutionDueAt,
            ], 'Tiket berhasil dibuat', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal membuat tiket: ' . $e->getMessage());
        }
    }

    public function updateTicketStatus(Request $request, $ticket_number)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
            'assign_by' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            $updateData = [
                'status' => $request->status,
                'updated_at' => now(),
            ];

            if ($request->filled('assign_by')) {
                $updateData['assign_by'] = $request->assign_by;
            } elseif (!$ticket->assign_by && Auth::user()) {
                $updateData['assign_by'] = Auth::user()->name;
            }

            // Track first response time if moved to in_progress or responded
            if ($request->status === 'in_progress' && !$ticket->first_responded_at) {
                $updateData['first_responded_at'] = now();
            }

            // Track resolution time and evaluate SLA breach if closed
            if ($request->status === 'closed') {
                $updateData['resolved_at'] = now();
                if ($ticket->resolution_due_at && Carbon::now()->greaterThan(Carbon::parse($ticket->resolution_due_at))) {
                    $updateData['is_sla_breached'] = true;
                }
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->update($updateData);

            AuditLogService::log(
                'STATUS_UPDATED',
                'Ticket',
                $ticket->id_ticket,
                ['status' => $ticket->status, 'assign_by' => $ticket->assign_by],
                $updateData
            );

            DB::commit();

            return $this->successResponse(null, 'Status tiket berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal memperbarui status tiket: ' . $e->getMessage());
        }
    }

    public function deleteTicket($ticket_number)
    {
        DB::beginTransaction();

        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            if ($ticket->attachment) {
                Storage::disk('public')->delete("attachments/{$ticket->attachment}");
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->delete();

            AuditLogService::log(
                'TICKET_DELETED',
                'Ticket',
                $ticket->id_ticket,
                ['ticket_number' => $ticket->ticket_number, 'subject' => $ticket->subject],
                null
            );

            DB::commit();

            return $this->successResponse(null, 'Tiket berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal menghapus tiket: ' . $e->getMessage());
        }
    }

    public function downloadAttachment($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket || !$ticket->attachment) {
                return $this->errorResponse('Lampiran tidak ditemukan', 404);
            }

            $filePath = storage_path("app/public/attachments/{$ticket->attachment}");
            if (!file_exists($filePath)) {
                $filePath = public_path("storage/attachments/{$ticket->attachment}");
            }

            if (!file_exists($filePath)) {
                return $this->errorResponse('File lampiran fisik tidak ditemukan di server', 404);
            }

            return response()->download($filePath);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengunduh lampiran: ' . $e->getMessage());
        }
    }

    /**
     * Export Berita Acara Penyelesaian Pekerjaan (BAPP) PDF
     */
    public function exportBap($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
                ->join('priority', 'tickets.priority_id', '=', 'priority.id')
                ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
                ->select(
                    'tickets.*',
                    'users.name as clientname',
                    'users.email as clientemail',
                    'kategoris.nama_kategori as kategori_name',
                    'priority.priority_name as priority',
                    'departments.name as department_name',
                    'departments.code as department_code'
                )
                ->where('tickets.ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            $pdf = Pdf::loadView('report.bap', compact('ticket'))
                ->setPaper('a4', 'portrait');

            $fileName = 'BAPP_' . $ticket->ticket_number . '_' . date('Ymd') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengekspor Berita Acara BAPP: ' . $e->getMessage());
        }
    }
}
