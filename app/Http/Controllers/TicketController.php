<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
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
                ->select('tickets.*', 'users.name as clientname', 'kategoris.nama_kategori as kategori_name')
                ->where('tickets.ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Ticket not found', 404);
            }

            if ($ticket->attachment) {
                $ticket->attachment_url = url('storage/' . $ticket->attachment);
            }
    

            return $this->successResponse($ticket);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve ticket. Please try again.');
        }
    }

    public function getTickets()
    {
        try {
            $tickets = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
                ->select('tickets.*', 'users.name as clientname', 'kategoris.nama_kategori as kategori_name')
                ->get();


                foreach ($tickets as $ticket) {
                    if ($ticket->attachment) {
                        $ticket->attachment_url = url('storage/' . $ticket->attachment);
                    }
                }
        
            return $this->successResponse($tickets);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve tickets. Please try again.');
        }
    }

    public function createTicket(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'issue' => 'required|string',
            'attachment' => 'nullable|file',
        ]);

        DB::beginTransaction();

        try {
            $filePath = null;
            if ($request->hasFile('attachment')) {
                $originalFileName = $request->file('attachment')->getClientOriginalName();
                $filePath = $request->file('attachment')->storeAs('attachments', $originalFileName, 'public');
            }

            $newTicketId = DB::table('tickets')->insertGetId([
                'user_id' => $userId,
                'status' => 'open',
                'kategori_id'=> $request->kategori_id,
                'issue' => $request->issue,
                'subject'=>$request->subject,
                'attachment' => $filePath,
                'attachment_name'=> $originalFileName,
                'created_at' => now(),
            ]);

            $ticketNumber = 'TIX-' . str_pad($newTicketId, 4, '0', STR_PAD_LEFT);

            DB::table('tickets')
                ->where('id_ticket', $newTicketId)
                ->update(['ticket_number' => $ticketNumber]);

            DB::commit();

            return $this->successResponse(['ticket_number' => $ticketNumber, 'attachment_url' => asset("storage/{$filePath}")], 'Ticket created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function updateTicketStatus(Request $request, $ticket_number)
    {
        DB::beginTransaction();

        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return $this->errorResponse('Ticket not found', 404);
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->update([
                    'status' => $request->status,
                    'assign_by' => Auth::user()->name,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return $this->successResponse(null, 'Ticket status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Ticket status update failed. Please try again.');
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
                return $this->errorResponse('Ticket not found', 404);
            }

            if ($ticket->attachment) {
                Storage::disk('public')->delete($ticket->attachment);
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->delete();

            DB::commit();

            return $this->successResponse(null, 'Ticket deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Ticket deletion failed. Please try again.');
        }
    }

    public function downloadAttachment($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket || !$ticket->attachment) {
                return $this->errorResponse('Attachment not found', 404);
            }

  return response()->download(public_path("storage/{$ticket->attachment}"));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download attachment. Please try again.');
        }
    }
}
