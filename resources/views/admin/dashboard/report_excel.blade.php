<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    @verbatim
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Laporan Pemesanan</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    @endverbatim
    <style>
        .num-text { mso-number-format:"\@"; }
        .currency { mso-number-format:"\#\,\#\#0"; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; font-family: Arial, sans-serif; font-size: 11pt; }
        .title-header { font-size: 14pt; font-weight: bold; color: #0f172a; }
        .subtitle-header { font-size: 11pt; color: #475569; }
        .kpi-title { font-size: 11pt; font-weight: bold; background-color: #f1f5f9; color: #0f172a; }
        .kpi-val { font-size: 11pt; font-weight: bold; }
        .th-main { background-color: #4f46e5; color: #ffffff; font-weight: bold; text-align: center; }
        .status-lunas { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
        .status-dp { background-color: #f3e8ff; color: #7e22ce; font-weight: bold; text-align: center; }
        .status-pending { background-color: #fef3c7; color: #b45309; font-weight: bold; text-align: center; }
        .status-batal { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <table>
        <!-- Header Homestay -->
        <tr>
            <td colspan="13" class="title-header" style="height: 30px;">{{ strtoupper($setting->homestay_name ?? 'GEDAMBAAN GLAMPING') }}</td>
        </tr>
        <tr>
            <td colspan="13" class="subtitle-header">LAPORAN BULANAN PEMESANAN HOMESTAY</td>
        </tr>
        <tr>
            <td colspan="13" class="subtitle-header">Periode: <b>{{ strtoupper($monthName) }} {{ $year }}</b></td>
        </tr>
        <tr>
            <td colspan="13" class="subtitle-header">Tanggal Export: {{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr><td colspan="13" style="border:none;"></td></tr>

        <!-- Ringkasan Eksekutif KPI -->
        <tr>
            <td colspan="4" class="kpi-title">RINGKASAN EKSEKUTIF BULANAN</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2">Total Pesanan</td>
            <td colspan="2" class="kpi-val" style="text-align: right;">{{ $summary['total'] }} Pesanan</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2">Pesanan Lunas / Selesai</td>
            <td colspan="2" class="kpi-val" style="text-align: right; color: #16a34a;">{{ $summary['lunas'] }} Pesanan</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2">DP 50%</td>
            <td colspan="2" class="kpi-val" style="text-align: right; color: #7e22ce;">{{ $summary['dp'] }} Pesanan</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2">Pending WA</td>
            <td colspan="2" class="kpi-val" style="text-align: right; color: #d97706;">{{ $summary['pending'] }} Pesanan</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2">Dibatalkan</td>
            <td colspan="2" class="kpi-val" style="text-align: right; color: #dc2626;">{{ $summary['batal'] }} Pesanan</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr>
            <td colspan="2" style="background-color: #fef3c7; font-weight: bold;">Total Pendapatan / Omset</td>
            <td colspan="2" class="kpi-val" style="background-color: #fef3c7; text-align: right; color: #b45309;">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</td>
            <td colspan="9" style="border:none;"></td>
        </tr>
        <tr><td colspan="13" style="border:none;"></td></tr>

        <!-- Table Data Header -->
        <thead>
            <tr>
                <th class="th-main" style="width: 40px;">No</th>
                <th class="th-main" style="width: 140px;">Kode Booking</th>
                <th class="th-main" style="width: 180px;">Nama Tamu</th>
                <th class="th-main" style="width: 140px;">No WhatsApp</th>
                <th class="th-main" style="width: 160px;">Kamar / Unit</th>
                <th class="th-main" style="width: 110px;">Check-In</th>
                <th class="th-main" style="width: 110px;">Check-Out</th>
                <th class="th-main" style="width: 100px;">Durasi</th>
                <th class="th-main" style="width: 180px;">Extra Fasilitas</th>
                <th class="th-main" style="width: 130px;">Diskon Admin (%)</th>
                <th class="th-main" style="width: 130px;">Total Biaya (Rp)</th>
                <th class="th-main" style="width: 120px;">Status</th>
                <th class="th-main" style="width: 140px;">Tanggal Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $idx => $b)
                @php
                    $extras = [];
                    if (is_array($b->extra_facilities)) {
                        foreach ($b->extra_facilities as $ef) {
                            $extras[] = $ef['name'] ?? '';
                        }
                    }
                    $extrasStr = count($extras) > 0 ? implode(', ', array_filter($extras)) : '-';
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td class="num-text" style="font-weight: bold; text-align: center;">{{ $b->booking_code }}</td>
                    <td><b>{{ $b->customer_name }}</b></td>
                    <td class="num-text">{{ $b->customer_phone }}</td>
                    <td>{{ $b->room->name ?? '-' }}</td>
                    <td style="text-align: center;">{{ $b->check_in_date ? $b->check_in_date->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: center;">{{ $b->check_out_date ? $b->check_out_date->format('d/m/Y') : '-' }}</td>
                    <td style="text-align: center;">{{ $b->total_nights }} Malam</td>
                    <td>{{ $extrasStr }}</td>
                    <td style="text-align: center; font-weight: bold; color: #ea580c;">{{ ($b->admin_discount && $b->admin_discount > 0) ? number_format($b->admin_discount, 0) . '%' : '-' }}</td>
                    <td class="currency" style="text-align: right; font-weight: bold;">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                    @if(in_array($b->status, [2, 3]))
                        <td class="status-lunas">LUNAS</td>
                    @elseif($b->status == 4)
                        <td class="status-dp">DP 50%</td>
                    @elseif($b->status == 1)
                        <td class="status-pending">PENDING</td>
                    @else
                        <td class="status-batal">DIBATALKAN</td>
                    @endif
                    <td style="text-align: center;">{{ $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; color: #94a3b8; padding: 15px;">
                        Tidak ada transaksi pemesanan pada bulan {{ $monthName }} {{ $year }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
