<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
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
    // Auth routes...
      //Auth
      Route::post('register', [AuthController::class, 'register']);
      Route::post('login', [AuthController::class, 'login']);
      Route::post('logout', [AuthController::class, 'logout']);
      Route::post('refresh', [AuthController::class, 'refresh']);
    //   Route::post('me', [AuthController::class, 'me']);
      //EndAuth
    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'getTickets']);
        Route::post('/', [TicketController::class, 'createTicket']);
        Route::put('/{ticket_number}', [TicketController::class, 'updateTicketStatus'])->middleware('roleCheck:admin,support');
        Route::delete('/{ticket_number}', [TicketController::class, 'deleteTicket']);
        Route::get('/{ticket_number}', [TicketController::class, 'getTicket']);
        Route::get('/download/{ticket_number}', [TicketController::class, 'downloadAttachment']);
    });
    // Kategori routes...
    Route::prefix('kategoris')->group(function () {
        Route::post('/', [KategoriController::class, 'createKategori'])->middleware('roleCheck:admin');
        Route::get('/', [KategoriController::class, 'getKategoris'])->middleware('roleCheck:admin');
        Route::get('/active', [KategoriController::class, 'getActiveKategoris']);
        Route::put('/{id}', [KategoriController::class, 'updateKategori'])->middleware('roleCheck:admin');
        Route::get('/{id}', [KategoriController::class, 'getKategori'])->middleware('roleCheck:admin');
        Route::delete('/{id}', [KategoriController::class, 'deleteKategori'])->middleware('roleCheck:admin');
    });

    // Users routes...
    Route::prefix('users')->middleware('roleCheck:admin')->group(function () {
        Route::get('/', [AuthController::class, 'getUsers']);
        Route::get('/{id}', [AuthController::class, 'getUser']);
        Route::put('/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/{id}', [AuthController::class, 'deleteUser']);
    });

    // Report routes...
    Route::get('/report', [ReportController::class, 'showReport'])->middleware('roleCheck:admin');

    // Comment routes...
    Route::prefix('comment')->middleware('roleCheck:admin,support,client')->group(function () {
        Route::get('/{ticket_id}', [CommentController::class, 'getComments']);
        Route::post('/', [CommentController::class, 'createComment']);
        Route::get('/download/{id}', [CommentController::class, 'downloadCommentAttachment']);
    });
});

