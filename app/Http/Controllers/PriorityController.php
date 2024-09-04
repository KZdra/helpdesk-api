<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriorityController extends Controller
{

    use ApiResponse;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

 public function fetchPriority(){
    try {
        $priority = DB::table('priority')->get();
        return $this->successResponse($priority);
    } catch (\Exception $e) {
        return $this->errorResponse('Failed to retrieve priority. Please try again.', 500);
    }
 }
}
