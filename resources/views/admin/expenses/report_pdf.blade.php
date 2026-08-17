<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Catatan Pengeluaran {{ $monthName }} {{ $year }} - {{ $setting->homestay_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 25px;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .homestay-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        .homestay-meta {
            font-size: 11px;
            color: #475569;
        }

        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .report-title p {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #64748b;
        }

        .kpi-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .kpi-card {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 15px;
            background: #f8fafc;
        }

        .kpi-card .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kpi-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .report-table th, .report-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }

        .report-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        .report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .amount-col {
            font-weight: bold;
            color: #e11d48;
            text-align: right;
        }

        .footer-sig {
            margin-top: 40px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .sig-box {
            text-align: center;
            width: 220px;
        }

        .no-print-bar {
            background: #0f172a;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -25px -25px 25px -25px;
        }

        .btn-print {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: #4338ca;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <i class="fa-solid fa-file-pdf" style="color: #f43f5e; margin-right: 8px;"></i>
            <strong>Pratinjau Laporan PDF Catatan Pengeluaran</strong>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
            <button class="btn-print" style="background: #64748b;" onclick="window.close()">
                <i class="fa-solid fa-xmark"></i> Tutup
            </button>
        </div>
    </div>

    <!-- Header / Kop Surat -->
    <table class="header-table">
        <tr>
            <td style="border: none; width: 70px; vertical-align: middle;">
                @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
                    <img src="/{{ ltrim($setting->logo, '/') }}" alt="Logo" style="max-width: 60px; max-height: 60px;">
                @else
                    <div style="font-size: 32px; color: #4f46e5;"><i class="fa-solid fa-house-chimney"></i></div>
                @endif
            </td>
            <td style="border: none; vertical-align: middle;">
                <div class="homestay-title">{{ $setting->homestay_name ?? 'GEDAMBAAN GLAMPING' }}</div>
                <div class="homestay-meta">{{ $setting->address ?? 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan' }}</div>
                <div class="homestay-meta">WhatsApp: {{ $setting->wa_number ?? '08776905151' }}</div>
            </td>
            <td style="border: none; text-align: right; vertical-align: middle;" class="homestay-meta">
                <div><strong>Dicetak Pada:</strong></div>
                <div>{{ date('d/m/Y H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <!-- Judul Laporan -->
    <div class="report-title">
        <h2>Laporan Catatan Pengeluaran Operasional</h2>
        <p>Periode: <strong>{{ strtoupper($monthName) }} {{ $year }}</strong></p>
    </div>

    <!-- KPI Ringkasan -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="label">Total Catatan Transaksi</div>
            <div class="value">{{ number_format($totalCount, 0, ',', '.') }} Transaksi</div>
        </div>
        <div class="kpi-card">
            <div class="label">Total Nominal Pengeluaran</div>
            <div class="value" style="color: #e11d48;">Rp {{ number_format($totalHarga, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Tabel Data -->
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">#</th>
                <th style="width: 90px;">Tanggal</th>
                <th>Nama Pengeluaran</th>
                <th style="width: 130px; text-align: right;">Nominal (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $index => $expense)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->tanggal)->format('d/m/Y') }}</td>
                <td><strong>{{ $expense->nama_pengeluaran }}</strong></td>
                <td class="amount-col">Rp {{ number_format($expense->harga, 0, ',', '.') }}</td>
                <td>{{ $expense->keterangan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px;">
                    Tidak ada data pengeluaran pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: bold;">
                <td colspan="3" style="text-align: right; font-size: 11px;">TOTAL PENGELUARAN:</td>
                <td class="amount-col" style="font-size: 12px; color: #e11d48;">Rp {{ number_format($totalHarga, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <div class="footer-sig">
        <div class="sig-box">
            <div>Kotabaru, {{ date('d F Y') }}</div>
            <div style="margin-top: 4px; font-weight: bold;">Pengelola / Admin Homestay</div>
            <div style="height: 60px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">({{ Auth::user()->name ?? 'Administrator' }})</div>
        </div>
    </div>

    <script>
        // Auto trigger print dialogue when loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
