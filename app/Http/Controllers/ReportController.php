<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * Query builder helper for report filtering
     */
    private function getFilteredReportQuery(Request $request)
    {
        $query = DB::table('tickets')
            ->join('users', 'tickets.user_id', '=', 'users.id')
            ->join('kategoris', 'tickets.kategori_id', '=', 'kategoris.id')
            ->leftJoin('priority', 'tickets.priority_id', '=', 'priority.id')
            ->select(
                'tickets.id_ticket',
                'tickets.ticket_number',
                'tickets.created_at',
                'tickets.subject',
                'tickets.issue',
                'tickets.status',
                'users.name as clientname',
                'kategoris.nama_kategori as kategori_name',
                'priority.priority_name as priority'
            );

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tickets.created_at', [$startDate, $endDate]);
        } elseif ($request->filled('start_date')) {
            $query->where('tickets.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } elseif ($request->filled('end_date')) {
            $query->where('tickets.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        // Category filter
        $categoryId = $request->input('category_id', $request->input('kategori_id'));
        if ($categoryId) {
            $query->where('tickets.kategori_id', $categoryId);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('tickets.status', $request->status);
        }

        // Priority filter (optional)
        if ($request->filled('priority_id')) {
            $query->where('tickets.priority_id', $request->priority_id);
        }

        return $query->orderBy('tickets.created_at', 'desc');
    }

    /**
     * Get Report Data (JSON)
     */
    public function showReport(Request $request)
    {
        try {
            $query = $this->getFilteredReportQuery($request);

            if ($request->input('paginate') === 'true') {
                $perPage = $request->input('per_page', 10);
                $data = $query->paginate((int) $perPage);
            } else {
                $data = $query->get();
            }

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengambil data laporan: ' . $e->getMessage());
        }
    }

    /**
     * Export Report to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $tickets = $this->getFilteredReportQuery($request)->get();

            $categoryId = $request->input('category_id', $request->input('kategori_id'));
            $categoryName = null;
            if ($categoryId) {
                $categoryName = DB::table('kategoris')->where('id', $categoryId)->value('nama_kategori');
            }

            $filters = [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'category_name' => $categoryName,
            ];

            $pdf = Pdf::loadView('report.pdf', compact('tickets', 'filters'))
                ->setPaper('a4', 'landscape');

            $fileName = 'Laporan_Tiket_' . date('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Report to Excel (XLSX)
     */
    public function exportExcel(Request $request)
    {
        try {
            $tickets = $this->getFilteredReportQuery($request)->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Tiket');

            // Header Title
            $sheet->setCellValue('A1', 'LAPORAN TIKET HELPDESK NEXTIX');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Filter Information
            $filterText = 'Tanggal Cetak: ' . date('d-m-Y H:i:s');
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $filterText .= ' | Periode: ' . $request->start_date . ' s/d ' . $request->end_date;
            }
            if ($request->filled('status')) {
                $filterText .= ' | Status: ' . strtoupper($request->status);
            }

            $sheet->setCellValue('A2', $filterText);
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table Column Headers
            $headers = ['No', 'Ticket Number', 'Tanggal', 'Client Name', 'Kategori', 'Subject', 'Status'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
            
            $headerRow = 4;
            foreach ($headers as $index => $header) {
                $sheet->setCellValue($cols[$index] . $headerRow, $header);
            }

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'] // Royal Blue
                ]
            ];
            $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
            $sheet->getRowDimension(4)->setRowHeight(26);

            // Data Rows
            $row = 5;
            foreach ($tickets as $idx => $ticket) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, $ticket->ticket_number);
                $sheet->setCellValue('C' . $row, date('d-m-Y', strtotime($ticket->created_at)));
                $sheet->setCellValue('D' . $row, $ticket->clientname);
                $sheet->setCellValue('E' . $row, $ticket->kategori_name);
                $sheet->setCellValue('F' . $row, $ticket->subject);
                $sheet->setCellValue('G' . $row, strtoupper($ticket->status));

                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row++;
            }

            $lastRow = $row - 1;
            if ($lastRow >= 4) {
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ];
                $sheet->getStyle('A4:G' . $lastRow)->applyFromArray($borderStyle);
            }

            // Auto-size columns
            foreach ($cols as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = 'Laporan_Tiket_' . date('Ymd_His') . '.xlsx';
            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Gagal mengekspor Excel: ' . $e->getMessage());
        }
    }
}
