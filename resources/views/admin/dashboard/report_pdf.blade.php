<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemesanan {{ $monthName }} {{ $year }} - {{ $setting->homestay_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
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
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .kpi-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #f8fafc;
        }

        .kpi-card .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .kpi-card .val {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .table-data th, .table-data td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            text-align: left;
        }

        .table-data th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 10px;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .status-lunas { background: #dcfce7; color: #166534; }
        .status-dp { background: #f3e8ff; color: #7e22ce; }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-batal { background: #fee2e2; color: #991b1b; }

        .signature-table {
            width: 100%;
            margin-top: 40px;
        }

        .btn-print-floating {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #4f46e5;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            border: none;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn-print-floating:hover {
            background: #4338ca;
        }

        @media print {
            .btn-print-floating { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <button type="button" class="btn-print-floating" onclick="window.print()">
        <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
    </button>

    <table class="header-table">
        <tr>
            <td style="width: 70px;">
                @if(isset($setting->logo) && file_exists(public_path($setting->logo)))
                    <img src="/{{ $setting->logo }}" alt="Logo" style="height: 50px; width: auto;">
                @else
                    <i class="fa-solid fa-house-chimney" style="font-size: 36px; color: #4f46e5;"></i>
                @endif
            </td>
            <td>
                <div class="homestay-title">{{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</div>
                <div class="homestay-meta">
                    {{ $setting->address ?? 'Banjarbaru, Kalimantan Selatan' }} • No. WA: +{{ preg_replace('/[^0-9]/', '', $setting->wa_number ?? '6281234567890') }}
                </div>
            </td>
            <td style="text-align: right; font-size: 10px; color: #64748b;">
                Tanggal Cetak: {{ date('d F Y H:i') }}<br>
                Dicetak Oleh: {{ auth()->user()->name ?? 'Admin' }}
            </td>
        </tr>
    </table>

    <div class="report-title">
        <h2>LAPORAN BULANAN PEMESANAN HOMESTAY</h2>
        <p>Periode: <strong>{{ $monthName }} {{ $year }}</strong></p>
    </div>

    <!-- Ringkasan KPI -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="label">Total Pesanan</div>
            <div class="val">{{ $summary['total'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Pesanan Lunas</div>
            <div class="val" style="color: #16a34a;">{{ $summary['lunas'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">DP 50%</div>
            <div class="val" style="color: #7e22ce;">{{ $summary['dp'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Pending</div>
            <div class="val" style="color: #d97706;">{{ $summary['pending'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Dibatalkan</div>
            <div class="val" style="color: #dc2626;">{{ $summary['batal'] }}</div>
        </div>
        <div class="kpi-card" style="background: #fef3c7;">
            <div class="label">Total Pendapatan</div>
            <div class="val" style="color: #b45309; font-size: 12px;">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Data Tabel -->
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Kode Booking</th>
                <th>Nama Tamu</th>
                <th>No. HP / WA</th>
                <th>Kamar / Unit</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th style="text-align: center;">Durasi</th>
                <th>Total Biaya</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $idx => $b)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $b->booking_code }}</td>
                    <td><strong>{{ $b->customer_name }}</strong></td>
                    <td>{{ $b->customer_phone }}</td>
                    <td>{{ $b->room->name ?? '-' }}</td>
                    <td>{{ $b->check_in_date ? $b->check_in_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $b->check_out_date ? $b->check_out_date->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: center;">{{ $b->total_nights }} Malam</td>
                    <td style="font-weight: bold; color: #0f172a;">
                        Rp {{ number_format($b->total_price, 0, ',', '.') }}
                        @if($b->admin_discount && $b->admin_discount > 0)
                            <div style="font-size: 8px; color: #ea580c; font-weight: normal;">Diskon Admin {{ number_format($b->admin_discount, 0) }}%</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(in_array($b->status, [2, 3]))
                            <span class="status-badge status-lunas">LUNAS</span>
                        @elseif($b->status == 4)
                            <span class="status-badge status-dp">DP 50%</span>
                        @elseif($b->status == 1)
                            <span class="status-badge status-pending">PENDING</span>
                        @else
                            <span class="status-badge status-batal">DIBATALKAN</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #94a3b8; padding: 20px;">
                        Tidak ada transaksi pemesanan pada bulan {{ $monthName }} {{ $year }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="text-align: center; font-size: 11px;">
                Banjarbaru, {{ date('d F Y') }}<br>
                <strong>Pengelola / Admin Homestay</strong>
                <br><br><br><br><br>
                ( {{ auth()->user()->name ?? 'Admin Gedambaan Glamping' }} )
            </td>
        </tr>
    </table>

</body>
</html>
