<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ReportController;
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

    // Tickets
    Route::prefix('tickets')->group(function () {

        Route::get('/', [TicketController::class, 'getTickets']);
        Route::post('/', [TicketController::class, 'createTicket']);
        Route::put('/{ticket_number}', [TicketController::class, 'updateTicketStatus']);
        Route::delete('/{ticket_number}', [TicketController::class, 'deleteTicket']);
        Route::get('/{ticket_number}', [TicketController::class, 'getTicket']);
        Route::get('/download/{ticket_number}', [TicketController::class, 'downloadAttachment']);
    });
    
    
    //
    Route::prefix('kategoris')->group(function () {

        Route::post('/', [KategoriController::class, 'createKategori']);
        Route::get('/', [KategoriController::class, 'getKategoris']);
        Route::get('/active', [KategoriController::class, 'getActiveKategoris']);
        Route::put('/{id}', [KategoriController::class, 'updateKategori']);
        Route::get('/{id}', [KategoriController::class, 'getKategori']);
        Route::delete('/{id}', [KategoriController::class, 'deleteKategori']);
    });
    //Users
    Route::prefix('users')->group(function () {
        Route::get('/', [AuthController::class,'getUsers']);
        Route::get('/{id}', [AuthController::class, 'getUser']);
        Route::put('/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/{id}', [AuthController::class,'deleteUser']);
    });
    //USERs Fetch
   

    //report
    Route::get('/report', [ReportController::class, 'showReport']);


});
