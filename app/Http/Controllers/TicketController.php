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
                $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
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
                        $ticket->attachment_url = url('storage/attachments/' . $ticket->attachment);
                    }
                }
        
            return $this->successResponse($tickets);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve tickets. Please try again.');
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
    
            // Commit the transaction
            DB::commit();
    
            return [
                'ticket_number' => $ticketNumber,
                'numbering_id' => $numberingId
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
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

            $ticketData = $this->generateTicketNumber();

            if ($request->hasFile('attachment')) {
                $originalFileName = $request->file('attachment')->getClientOriginalName();
                $filePath = $request->file('attachment')->storeAs('attachments', $originalFileName, 'public');
            }

            DB::table('tickets')->insert([
                'user_id' => $userId,
                'ticket_number'=> $ticketData['ticket_number'],
                'numbering_id' => $ticketData['numbering_id'],
                'status' => 'open',
                'kategori_id'=> $request->kategori_id,
                'issue' => $request->issue,
                'subject'=>$request->subject,
                'attachment' => $originalFileName,
                'created_at' => now(),
            ]);


           
            DB::commit();

            return $this->successResponse(['ticket_number' => $ticketData['ticket_number']], 'Ticket created successfully', 201);
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
                Storage::disk('public')->delete("attachments/{$ticket->attachments}");
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

  return response()->download(public_path("storage/attachments/{$ticket->attachment}"));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to download attachment. Please try again.');
        }
    }
}
