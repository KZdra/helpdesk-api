<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function getTicketStatistics()
    {
        $ticketCounts = DB::table('tickets')
            ->select(DB::raw('status, COUNT(*) as count'))
            ->whereIn('status', ['closed', 'open'])
            ->groupBy('status')
            ->get();
    
        // Mengubah hasil query menjadi array dengan keys 'closed' dan 'open'
        $ticketStats = [
            'closed' => 0,
            'open' => 0
        ];
    
        foreach ($ticketCounts as $ticket) {
            $ticketStats[$ticket->status] = $ticket->count;
        }
    
        return $this->successResponse(['Ticket'=>$ticketStats], 'Ticket statistics fetched successfully');
    }
    
    public function getTicketStatisticsByUser() {
        $user_id = Auth::id();

        $ticketCounts = DB::table('tickets')
        ->where('user_id', $user_id)
        ->select(DB::raw('status, COUNT(*) as count'))
        ->whereIn('status', ['closed', 'open'])
        ->groupBy('status')
        ->get();

    // Mengubah hasil query menjadi array dengan keys 'closed' dan 'open'
    $ticketStats = [
        'closed' => 0,
        'open' => 0
    ];

    foreach ($ticketCounts as $ticket) {
        $ticketStats[$ticket->status] = $ticket->count;
    }

    return $this->successResponse(['Ticket'=>$ticketStats], 'Ticket statistics fetched successfully');


    }

    public function getUsersStatistics() {
        $users = DB::table('users')->count();
        return $this->successResponse(['UserRegistered'=>$users], 'Users statistics fetched successfully');
    }
}
