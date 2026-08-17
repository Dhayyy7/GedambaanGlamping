<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    @verbatim
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Laporan Pengeluaran</x:Name>
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
        .date-fmt { mso-number-format:"yyyy-mm-dd"; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; font-family: Arial, sans-serif; font-size: 11pt; }
        .title-header { font-size: 14pt; font-weight: bold; color: #0f172a; }
        .subtitle-header { font-size: 11pt; color: #475569; }
        .kpi-title { font-size: 11pt; font-weight: bold; background-color: #f1f5f9; color: #0f172a; }
        .kpi-val { font-size: 11pt; font-weight: bold; }
        .th-main { background-color: #e11d48; color: #ffffff; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <table>
        <!-- Header Homestay -->
        <tr>
            <td colspan="5" class="title-header" style="height: 30px;">{{ strtoupper($setting->homestay_name ?? 'GEDAMBAAN GLAMPING') }}</td>
        </tr>
        <tr>
            <td colspan="5" class="subtitle-header">LAPORAN CATATAN PENGELUARAN OPERASIONAL</td>
        </tr>
        <tr>
            <td colspan="5" class="subtitle-header">Periode: <b>{{ strtoupper($monthName) }} {{ $year }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="subtitle-header">Tanggal Export: {{ date('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr><td colspan="5" style="border:none;"></td></tr>

        <!-- Ringkasan Eksekutif KPI -->
        <tr>
            <td colspan="2" class="kpi-title">RINGKASAN CATATAN PENGELUARAN</td>
            <td colspan="3" style="border:none;"></td>
        </tr>
        <tr>
            <td>Total Catatan Transaksi</td>
            <td class="kpi-val" style="text-align: right;">{{ $totalCount }} Transaksi</td>
            <td colspan="3" style="border:none;"></td>
        </tr>
        <tr>
            <td>Total Nominal Pengeluaran</td>
            <td class="kpi-val currency" style="text-align: right; color: #e11d48;">{{ $totalHarga }}</td>
            <td colspan="3" style="border:none;"></td>
        </tr>
        <tr><td colspan="5" style="border:none;"></td></tr>

        <!-- Table Data Header -->
        <thead>
            <tr>
                <th class="th-main" style="width: 40px;">#</th>
                <th class="th-main" style="width: 120px;">Tanggal</th>
                <th class="th-main" style="width: 250px;">Nama Pengeluaran</th>
                <th class="th-main" style="width: 160px;">Harga / Nominal (Rp)</th>
                <th class="th-main" style="width: 300px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $index => $expense)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;" class="date-fmt">{{ \Carbon\Carbon::parse($expense->tanggal)->format('Y-m-d') }}</td>
                <td><b>{{ $expense->nama_pengeluaran }}</b></td>
                <td class="currency" style="text-align: right; font-weight: bold; color: #e11d48;">{{ $expense->harga }}</td>
                <td>{{ $expense->keterangan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8;">Belum ada catatan pengeluaran.</td>
            </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" style="text-align: right;">TOTAL PENGELUARAN:</td>
                <td class="currency" style="text-align: right; color: #e11d48; font-weight: bold;">{{ $totalHarga }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
