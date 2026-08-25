<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Berita Acara Penyelesaian Pekerjaan - {{ $ticket->ticket_number }}</title>
    <style>
        @page {
            margin: 25px 35px 25px 35px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .instansi-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0;
        }
        .sub-instansi {
            font-size: 11px;
            color: #334155;
            margin: 2px 0;
        }
        .bapp-title-box {
            text-align: center;
            margin: 15px 0 20px 0;
        }
        .bapp-title {
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 0;
        }
        .bapp-no {
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }
        .intro-text {
            text-align: justify;
            margin-bottom: 12px;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .table-info {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .table-info td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .table-info td.label {
            width: 25%;
            font-weight: bold;
            color: #334155;
        }
        .table-info td.colon {
            width: 2%;
            text-align: center;
        }
        .table-info td.val {
            width: 73%;
        }
        .box-highlight {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-closed {
            background-color: #dcfce7;
            color: #15803d;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-role {
            font-size: 10px;
            color: #64748b;
        }
        .footer-note {
            margin-top: 25px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <div class="header">
        <div class="instansi-title">PEMERINTAH DAERAH / INSTANSI RESMI</div>
        <div class="sub-instansi">LAYANAN HELPDESK & DUKUNGAN TEKNOLOGI INFORMASI (NEXTIX)</div>
        <div class="sub-instansi" style="font-size: 9px; color: #64748b;">Layanan Tata Kelola Insiden & Permintaan Layanan IT Terpadu (Standar ITIL / SPBE)</div>
    </div>

    <!-- Judul Dokumen -->
    <div class="bapp-title-box">
        <h2 class="bapp-title">BERITA ACARA PENYELESAIAN PEKERJAAN (BAPP)</h2>
        <div class="bapp-no">Nomor: BAPP/{{ date('Y/m', strtotime($ticket->resolved_at ?? $ticket->created_at)) }}/{{ $ticket->ticket_number }}</div>
    </div>

    <div class="intro-text">
        Pada hari ini <strong>{{ \Carbon\Carbon::parse($ticket->resolved_at ?? $ticket->created_at)->translatedFormat('l, d F Y') }}</strong>, telah dilakukan tindakan pemeriksaan, penanganan, dan perbaikan perangkat/layanan teknologi informasi atas pengajuan tiket resmi dengan rincian sebagai berikut:
    </div>

    <!-- Rincian Tiket -->
    <div class="section-title">I. Identitas Pengajuan & Pelapor</div>
    <div class="box-highlight">
        <table class="table-info">
            <tr>
                <td class="label">Nomor Tiket</td>
                <td class="colon">:</td>
                <td class="val" style="font-weight: bold; color: #1e3a8a;">{{ $ticket->ticket_number }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pelapor / Pengguna</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->clientname }}</td>
            </tr>
            <tr>
                <td class="label">Unit Kerja / Departemen</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->department_name ?? 'Unit Layanan Instansi' }}</td>
            </tr>
            <tr>
                <td class="label">Kategori Permasalahan</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->kategori_name }}</td>
            </tr>
            <tr>
                <td class="label">Tipe Layanan (ITIL)</td>
                <td class="colon">:</td>
                <td class="val">
                    {{ strtoupper(str_replace('_', ' ', $ticket->ticket_type ?? 'incident')) }}
                </td>
            </tr>
            <tr>
                <td class="label">Tingkat Prioritas</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->priority }}</td>
            </tr>
        </table>
    </div>

    <!-- Rincian Waktu & SLA -->
    <div class="section-title">II. Waktu Penanganan & Kepatuhan SLA</div>
    <div class="box-highlight">
        <table class="table-info">
            <tr>
                <td class="label">Waktu Tiket Dibuat</td>
                <td class="colon">:</td>
                <td class="val">{{ date('d-m-Y H:i:s', strtotime($ticket->created_at)) }} WIB</td>
            </tr>
            <tr>
                <td class="label">Respon Pertama Teknisi</td>
                <td class="colon">:</td>
                <td class="val">
                    {{ $ticket->first_responded_at ? date('d-m-Y H:i:s', strtotime($ticket->first_responded_at)) . ' WIB' : '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Waktu Selesai (Resolved)</td>
                <td class="colon">:</td>
                <td class="val">
                    {{ $ticket->resolved_at ? date('d-m-Y H:i:s', strtotime($ticket->resolved_at)) . ' WIB' : date('d-m-Y H:i:s', strtotime($ticket->updated_at)) . ' WIB' }}
                </td>
            </tr>
            <tr>
                <td class="label">Status Akhir</td>
                <td class="colon">:</td>
                <td class="val">
                    <span class="badge badge-closed">{{ strtoupper($ticket->status) }}</span>
                    @if($ticket->is_sla_breached)
                        <span style="color: #dc2626; font-weight: bold; margin-left: 10px;">(SLA Breached)</span>
                    @else
                        <span style="color: #16a34a; font-weight: bold; margin-left: 10px;">(SLA Met - On Time)</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Rincian Kendala & Solusi -->
    <div class="section-title">III. Rincian Kendala & Tindakan Teknisi</div>
    <div class="box-highlight">
        <table class="table-info">
            <tr>
                <td class="label">Subjek Permasalahan</td>
                <td class="colon">:</td>
                <td class="val" style="font-weight: bold;">{{ $ticket->subject }}</td>
            </tr>
            <tr>
                <td class="label">Deskripsi Kendala Pelapor</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->issue }}</td>
            </tr>
            <tr>
                <td class="label">Petugas / Teknisi Penangan</td>
                <td class="colon">:</td>
                <td class="val">{{ $ticket->assign_by ?? 'Tim Helpdesk IT' }}</td>
            </tr>
        </table>
    </div>

    <div class="intro-text" style="margin-top: 15px;">
        Demikian Berita Acara Penyelesaian Pekerjaan ini dibuat dengan sebenar-benarnya sesuai kondisi riil penanganan untuk dapat dipergunakan sebagaimana mestinya sebagai bukti kepatuhan layanan dan pertanggungjawaban tata kelola IT.
    </div>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                <div>Pihak Pertama (Pelapor / Pengguna),</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $ticket->clientname }}</div>
                <div class="signature-role">{{ $ticket->department_name ?? 'Pelapor Layanan' }}</div>
            </td>
            <td>
                <div>Pihak Kedua (Petugas IT / Teknisi),</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $ticket->assign_by ?? 'Petugas Helpdesk IT' }}</div>
                <div class="signature-role">Unit Layanan Teknologi Informasi</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara sah dan otomatis oleh Sistem Helpdesk NexTix Terstandarisasi SPBE.
    </div>
</body>
</html>
