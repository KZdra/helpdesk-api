<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Tiket Helpdesk</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12px;
            margin: 20px;
        }
        .header-container {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 5px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 11px;
        }
        .meta-table td {
            padding: 3px 0;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table-data th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #1d4ed8;
        }
        .table-data td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .table-data tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-open {
            background-color: #fef3c7;
            color: #d97706;
        }
        .badge-in_progress {
            background-color: #e0f2fe;
            color: #0284c7;
        }
        .badge-closed {
            background-color: #dcfce7;
            color: #16a34a;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="title">NexTix Helpdesk</h1>
                    <div class="subtitle">Laporan Rekapitulasi Tiket</div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <div class="subtitle">Dicetak pada: {{ date('d F Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Periode:</strong></td>
            <td>
                @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                    {{ date('d-m-Y', strtotime($filters['start_date'])) }} s/d {{ date('d-m-Y', strtotime($filters['end_date'])) }}
                @elseif(!empty($filters['start_date']))
                    Mulai {{ date('d-m-Y', strtotime($filters['start_date'])) }}
                @elseif(!empty($filters['end_date']))
                    Sampai {{ date('d-m-Y', strtotime($filters['end_date'])) }}
                @else
                    Semua Periode
                @endif
            </td>
            <td style="width: 15%; text-align: right;"><strong>Status:</strong></td>
            <td style="width: 20%; text-align: right;">
                {{ !empty($filters['status']) ? strtoupper($filters['status']) : 'SEMUA' }}
            </td>
        </tr>
        <tr>
            <td><strong>Total Data:</strong></td>
            <td>{{ count($tickets) }} Tiket</td>
            <td style="text-align: right;"><strong>Kategori:</strong></td>
            <td style="text-align: right;">
                {{ !empty($filters['category_name']) ? $filters['category_name'] : 'SEMUA' }}
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th class="text-center" style="width: 18%;">Ticket Number</th>
                <th class="text-center" style="width: 14%;">Tanggal</th>
                <th style="width: 16%;">Client Name</th>
                <th style="width: 14%;">Kategori</th>
                <th style="width: 21%;">Subject</th>
                <th class="text-center" style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $index => $ticket)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center" style="font-weight: bold; color: #1e40af;">{{ $ticket->ticket_number }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($ticket->created_at)) }}</td>
                    <td>{{ $ticket->clientname }}</td>
                    <td>{{ $ticket->kategori_name }}</td>
                    <td>{{ $ticket->subject }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $ticket->status }}">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data tiket yang ditemukan untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem Helpdesk NexTix
    </div>
</body>
</html>
