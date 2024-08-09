<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                ->select('tickets.*', 'users.name as clientname')
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
            // Fetch tickets with clientname
            $tickets = DB::table('tickets')
                ->join('users', 'tickets.user_id', '=', 'users.id')
                ->select('tickets.*', 'users.name as clientname')
                ->get();

            return response()->json($tickets, 200);
        } catch (\Exception $e) {
            // Handle the exception (log it, return an error response, etc.)
            return response()->json(['error' => 'Failed to retrieve tickets. Please try again.'], 500);
        }
    }

    public function createTicket(Request $request)
    {
        $userId = Auth::id();

        DB::beginTransaction();

        try {
            // Create a new ticket
            $newTicketId = DB::table('tickets')->insertGetId([
                'user_id' => $userId,
                'status' => 'open',
                'issue' => $request->issue,
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

            return response()->json(['ticket_number' => $ticketNumber], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the exception (log it, return an error response, etc.)
            return response()->json(['error' => 'Ticket creation failed. Please try again.', $e], 500);
        }
    }

    public function updateTicket(Request $request, $ticket_number)
    {
        // Validate the request...

        DB::beginTransaction();

        try {
            // Find the ticket by ticket_number and update it
            $ticket = DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->first();

            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            // Update the ticket
            DB::table('tickets')
                ->where('ticket_number', $ticket_number)
                ->update([
                    'status' => $request->status,
                    'issue' => $request->issue,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return response()->json(['message' => 'Ticket updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ticket update failed. Please try again.'], 500);
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
}
