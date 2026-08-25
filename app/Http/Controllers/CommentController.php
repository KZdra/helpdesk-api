<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function getComments($ticket_id)
    {
        try {
            // Find ticket by id_ticket or ticket_number
            $ticket = DB::table('tickets')
                ->where('id_ticket', $ticket_id)
                ->orWhere('ticket_number', $ticket_id)
                ->first();

            $targetId = $ticket ? $ticket->id_ticket : $ticket_id;

            $comments = DB::table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->where('comments.ticket_id', $targetId)
                ->select(
                    'comments.*',
                    'users.name as user_name',
                    'roles.name as user_role'
                )
                ->orderBy('comments.created_at', 'asc')
                ->get();

            foreach ($comments as $comment) {
                if ($comment->attachment) {
                    $comment->attachment_url = url('storage/attachments/' . $comment->attachment);
                }
            }

            return $this->successResponse($comments);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil komentar: ' . $e->getMessage());
        }
    }

    public function createComment(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required',
            'comment' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Support both id_ticket and ticket_number
            $ticket = DB::table('tickets')
                ->where('id_ticket', $request->ticket_id)
                ->orWhere('ticket_number', $request->ticket_id)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Tiket tidak ditemukan', 404);
            }

            $originalFileName = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalFileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('attachments', $originalFileName, 'public');
            }

            $commentData = [
                'ticket_id' => $ticket->id_ticket,
                'user_id' => $userId,
                'comment' => $request->comment,
                'attachment' => $originalFileName,
                'created_at' => now(),
            ];

            $commentId = DB::table('comments')->insertGetId($commentData);

            // If user is technician/admin (role_id 1 or 2) and first_responded_at is null, update it
            if (in_array($user->role_id, [1, 2]) && !$ticket->first_responded_at && $ticket->user_id !== $userId) {
                DB::table('tickets')->where('id_ticket', $ticket->id_ticket)->update([
                    'first_responded_at' => now(),
                    'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
                    'updated_at' => now(),
                ]);
            }

            AuditLogService::log(
                'COMMENT_ADDED',
                'TicketComment',
                $commentId,
                null,
                [
                    'ticket_number' => $ticket->ticket_number,
                    'comment' => substr($request->comment, 0, 100) . (strlen($request->comment) > 100 ? '...' : '')
                ]
            );

            DB::commit();

            return $this->successResponse(['id' => $commentId], 'Komentar berhasil dikirim', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal mengirim komentar: ' . $e->getMessage());
        }
    }

    public function downloadCommentAttachment($id)
    {
        try {
            $comment = DB::table('comments')
                ->where('id', $id)
                ->first();

            if (!$comment || !$comment->attachment) {
                return $this->errorResponse('Lampiran komentar tidak ditemukan', 404);
            }

            $filePath = storage_path("app/public/attachments/{$comment->attachment}");
            if (!file_exists($filePath)) {
                $filePath = public_path("storage/attachments/{$comment->attachment}");
            }

            if (!file_exists($filePath)) {
                return $this->errorResponse('File lampiran fisik tidak ditemukan di server', 404);
            }

            return response()->download($filePath);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengunduh lampiran komentar: ' . $e->getMessage());
        }
    }
}
