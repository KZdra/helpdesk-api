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
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $category_id = $request->category_id;

        $data = DB::table('tickets')
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
            ->select('tickets.*', 'users.name as clientname', 'kategoris.nama_kategori as kategori_name');
        
        if ($startDate && $endDate) {
            $data->whereBetween('tickets.created_at', [$startDate, $endDate]);
        }
        if ($category_id) {
            $data->where('category_id', $category_id);
        }
        

        return $this->successResponse($data);
    }
}
