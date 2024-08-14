<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\KategoriController;
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
    //Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    //EndAuth

    // GetAllTicket
    Route::get('/tickets', [TicketController::class, 'getTickets']);
    // POST NEW TICKET
    Route::post('/tickets', [TicketController::class, 'createTicket']);
    // Update ticket
    // Route::put('/tickets/{ticket_number}', [TicketController::class, 'updateTicket']);

    // Update ticket status
    Route::put('/tickets/{ticket_number}', [TicketController::class, 'updateTicketStatus']);

    // Delete ticket
    Route::delete('/tickets/{ticket_number}', [TicketController::class, 'deleteTicket']);

    // Get a single ticket by ticket_number (Details)
    Route::get('/tickets/{ticket_number}', [TicketController::class, 'getTicket']);
    //Download Attachment
    Route::get('/tickets/download/{ticket_number}', [TicketController::class, 'downloadAttachment']);


    //
    Route::post('/kategoris', [KategoriController::class, 'createKategori']);
    Route::get('/kategoris', [KategoriController::class, 'getKategoris']);
    Route::get('/activekategoris', [KategoriController::class, 'getActiveKategoris']);
    Route::put('/kategoris/{id}', [KategoriController::class, 'updateKategori']);
    Route::get('/kategoris/{id}', [KategoriController::class, 'getKategori']);

    Route::delete('/kategoris/{id}', [KategoriController::class, 'deleteKategori']);
});
