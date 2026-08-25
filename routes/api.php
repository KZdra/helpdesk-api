<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\TicketController;
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
    // Auth
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'getTickets']);
        Route::post('/', [TicketController::class, 'createTicket']);
        Route::get('/user', [TicketController::class, 'getUserTickets']);
        Route::get('/sla-stats', [SlaController::class, 'getSlaStats'])->middleware('roleCheck:1,2');
        Route::put('/{ticket_number}', [TicketController::class, 'updateTicketStatus'])->middleware('roleCheck:1,2');
        Route::delete('/{ticket_number}', [TicketController::class, 'deleteTicket']);
        Route::get('/{ticket_number}', [TicketController::class, 'getTicket']);
        Route::get('/download/{ticket_number}', [TicketController::class, 'downloadAttachment']);
        Route::get('/{ticket_number}/export-bap', [TicketController::class, 'exportBap']);
        Route::post('/{ticket_id}/rate', [RatingController::class, 'submitRating']);
        Route::get('/{ticket_id}/rating', [RatingController::class, 'getTicketRating']);
    });

    // Departments / Unit Kerja OPD
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'getDepartments']);
        Route::get('/active', [DepartmentController::class, 'getActiveDepartments']);
        Route::get('/{id}', [DepartmentController::class, 'getDepartment']);
        Route::post('/', [DepartmentController::class, 'createDepartment'])->middleware('roleCheck:1');
        Route::put('/{id}', [DepartmentController::class, 'updateDepartment'])->middleware('roleCheck:1');
        Route::delete('/{id}', [DepartmentController::class, 'deleteDepartment'])->middleware('roleCheck:1');
    });

    // SLA Policies
    Route::prefix('sla')->group(function () {
        Route::get('/policies', [SlaController::class, 'getPolicies']);
        Route::get('/policies/{id}', [SlaController::class, 'getPolicy']);
        Route::put('/policies/{id}', [SlaController::class, 'updatePolicy'])->middleware('roleCheck:1');
    });

    // Kategori routes...
    Route::prefix('kategoris')->group(function () {
        Route::post('/', [KategoriController::class, 'createKategori'])->middleware('roleCheck:1');
        Route::get('/', [KategoriController::class, 'getKategoris'])->middleware('roleCheck:1');
        Route::get('/active', [KategoriController::class, 'getActiveKategoris']);
        Route::put('/{id}', [KategoriController::class, 'updateKategori'])->middleware('roleCheck:1');
        Route::get('/{id}', [KategoriController::class, 'getKategori'])->middleware('roleCheck:1');
        Route::delete('/{id}', [KategoriController::class, 'deleteKategori'])->middleware('roleCheck:1');
    });

    // Users routes...
    Route::prefix('users')->middleware('roleCheck:1')->group(function () {
        Route::get('/', [AuthController::class, 'getUsers']);
        Route::get('/roles', [AuthController::class, 'getRoles']);
        Route::get('/{id}', [AuthController::class, 'getUser']);
        Route::put('/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/{id}', [AuthController::class, 'deleteUser']);
    });

    // Report routes...
    Route::prefix('report')->middleware('roleCheck:1')->group(function () {
        Route::get('/', [ReportController::class, 'showReport']);
        Route::get('/export/pdf', [ReportController::class, 'exportPdf']);
        Route::get('/export/excel', [ReportController::class, 'exportExcel']);
        Route::get('/csat', [RatingController::class, 'getCsatReport']);
    });

    // Audit Trail Logs (SPBE Compliance)
    Route::prefix('audit-logs')->middleware('roleCheck:1')->group(function () {
        Route::get('/', [AuditLogController::class, 'getLogs']);
    });

    // Comment routes...
    Route::prefix('comment')->middleware('roleCheck:1,2,3')->group(function () {
        Route::get('/{ticket_id}', [CommentController::class, 'getComments']);
        Route::post('/', [CommentController::class, 'createComment']);
        Route::get('/download/{id}', [CommentController::class, 'downloadCommentAttachment']);
    });
    
    // Statistic
    Route::prefix('statistics')->group(function () {
        Route::get('/users', [StatisticController::class, 'getUsersStatistics'])->middleware('roleCheck:1');
        Route::get('/tickets', [StatisticController::class, 'getTicketStatistics'])->middleware('roleCheck:1,2');
        Route::get('/usertickets', [StatisticController::class, 'getTicketStatisticsByUser']);
    });

    // FAQs
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'getFaqs']);
        Route::get('/{id}', [FaqController::class, 'getFaq'])->middleware('roleCheck:1');
        Route::post('/', [FaqController::class, 'postFaqs'])->middleware('roleCheck:1');
        Route::put('/{id}', [FaqController::class, 'updateFaq'])->middleware('roleCheck:1');
        Route::delete('/{id}', [FaqController::class, 'deleteFaq'])->middleware('roleCheck:1');
    });

    // Priority
    Route::prefix('priority')->group(function () {
        Route::get('/', [PriorityController::class, 'fetchPriority']);
    });
});
