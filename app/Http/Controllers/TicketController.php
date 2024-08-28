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
    // Start a DB transaction
    DB::beginTransaction();

    try {
        // Get the current date in the desired format (year, month, day)
        $date = date('ymd'); // e.g., '240828' for August 28, 2024

        // Retrieve the numbering record for the current date
        $numbering = DB::table('numberings')
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->lockForUpdate()
            ->first();

        if ($numbering) {
            // If a numbering record exists for today, increment the sequence number
            $nextSequenceNumber = $numbering->sequence_number + 1;

            // Update the sequence number in the numbering table
            DB::table('numberings')
                ->where('id', $numbering->id)
                ->update(['sequence_number' => $nextSequenceNumber]);
        } else {
            // If no numbering record exists for today, create one starting with sequence 1
            $nextSequenceNumber = 1;

            $numberingId = DB::table('numberings')->insertGetId([
                'sequence_number' => $nextSequenceNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Format the next sequential number to be three digits with leading zeros
        $nextSequenceNumberFormatted = str_pad($nextSequenceNumber, 3, '0', STR_PAD_LEFT);

        // Generate the new ticket number
        $ticketNumber = 'TIX-' . $date . $nextSequenceNumberFormatted;

        // If we created a new numbering record, use its ID
        if (!isset($numberingId)) {
            $numberingId = $numbering->id;
        }

        // Commit the transaction
        DB::commit();

        return [
            'ticket_number' => $ticketNumber,
            'numbering_id' => $numberingId
        ];
    } catch (\Exception $e) {
        // Rollback the transaction on error
        DB::rollBack();
        throw $e; // Re-throw the exception for further handling
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
                'numering_id' => $ticketData['numbering_id'],
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
