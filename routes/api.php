<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);


    // Get all tickets (if needed)
    Route::get('/tickets', [TicketController::class, 'getTickets']);
    // POST NEW TICKET 
    Route::post('/tickets', [TicketController::class, 'createTicket']);
    // Update ticket
    Route::put('/tickets/{ticket_number}', [TicketController::class, 'updateTicket']);

    // Update ticket status
    Route::put('/tickets/{ticket_number}/status', [TicketController::class, 'updateTicketStatus']);

    // Delete ticket
    Route::delete('/tickets/{ticket_number}', [TicketController::class, 'deleteTicket']);

    // Get a single ticket by ticket_number (if needed)
    Route::get('/tickets/{ticket_number}', [TicketController::class, 'getTicket']);
});
