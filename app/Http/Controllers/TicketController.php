<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function getTicket($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id') // Join kategoris table
                ->select('tickets.*', 'users.name as clientname', 'kategoris.nama_kategori as kategori_name') // Include kategori name
                ->where('tickets.ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            return response()->json($ticket, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve ticket. Please try again.'], 500);
        }
    }

    public function getTickets()
    {
        try {
            // Fetch tickets with clientname and kategori name
            $tickets = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id') // Join kategoris table
                ->select('tickets.*', 'users.name as clientname', 'kategoris.nama_kategori as kategori_name') // Include kategori name
                ->get();

            return response()->json($tickets, 200);
        } catch (\Exception $e) {
            // Handle the exception (log it, return an error response, etc.)
            return response()->json(['error' => 'Failed to retrieve tickets. Please try again.',$e], 500);
        }
    }

    public function createTicket(Request $request)
    {
        $userId = Auth::id();

        // Validate the request input including the file
        $request->validate([
            'issue' => 'required|string',
            'attachment' => 'nullable|file', // Allow specific file types and limit the file size
        ]);

        DB::beginTransaction();

        try {
            // Handle file upload if it exists
            $filePath = null;
            if ($request->hasFile('attachment')) {
                // Get the original file name
                $originalFileName = $request->file('attachment')->getClientOriginalName();
                
                // Store the file with the original name
                $filePath = $request->file('attachment')->storeAs('attachments', $originalFileName, 'public');
            }
            

            // Create a new ticket
            $newTicketId = DB::table('tickets')->insertGetId([
                'user_id' => $userId,
                'status' => 'open',
                'kategori_id'=> $request->kategori,
                'issue' => $request->issue,
                'subject'=>$request->subject,
                'attachment' => $filePath, // Save the file path to the database
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate the ticket number
            $ticketNumber = 'TIX-' . str_pad($newTicketId, 4, '0', STR_PAD_LEFT);

            // Update the ticket with the generated ticket number
            DB::table('tickets')
                ->where('id_ticket', $newTicketId)
                ->update(['ticket_number' => $ticketNumber]);

            // Commit the transaction
            DB::commit();

            return response()->json(['ticket_number' => $ticketNumber, 'attachment_url' => asset("storage/{$filePath}")], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the exception (log it, return an error response, etc.)
            return response()->json(['error' => 'Ticket creation failed. Please try again.',$e], 500);
        }
    }

    public function updateTicketStatus(Request $request, $ticket_number)
    {
        // Validate the request...

        DB::beginTransaction();

        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->update([
                    'status' => $request->status,
                    'assign_by' => Auth::user()->name, // Capture the name of the user assigning the ticket
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json(['message' => 'Ticket status updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ticket status update failed. Please try again.'], 500);
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
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            // Delete the attachment file if it exists
            if ($ticket->attachment) {
                Storage::disk('public')->delete($ticket->attachment);
            }

            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->delete();

            DB::commit();

            return response()->json(['message' => 'Ticket deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ticket deletion failed. Please try again.'], 500);
        }
    }

    public function downloadAttachment($ticket_number)
    {
        try {
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket || !$ticket->attachment) {
                return response()->json(['error' => 'Attachment not found'], 404);
            }

            // Return the file as a response using the public URL
            return response()->download(public_path("storage/{$ticket->attachment}"));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to download attachment. Please try again.'], 500);
        }
    }
}
