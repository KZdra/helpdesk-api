<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ApiResponse;
    public function showReport(Request $request)
    {
        $option = $request->input('option');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $category_id = $request->input('category_id');

        switch ($option) {
            case 'all':
                $data = DB::table('tickets')->get();
                break;
                
            case 'date':
                $data = DB::table('tickets')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                break;
                
            case 'category':
                $data = DB::table('tickets')
                    ->where('kategori_id', $category_id)
                    ->get();
                break;

            default:
                return response()->json(['message' => 'Invalid option'], 400);
        }

        return $this->successResponse($data);
    }
}
