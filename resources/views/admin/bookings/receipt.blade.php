<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran #{{ $booking->booking_code }} - {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 40px 20px;
        }

        .receipt-card {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .homestay-title {
            font-size: 22px;
            font-weight: 800;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }

        .homestay-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        .receipt-badge-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            text-align: right;
        }

        .receipt-code-display {
            font-family: monospace;
            font-size: 13px;
            font-weight: 700;
            color: #4f46e5;
            text-align: right;
            margin-top: 4px;
        }

        .grid-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }

        .meta-box h4 {
            margin: 0 0 6px 0;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .meta-box p {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .table-items th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-items td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-lunas { background: #dcfce7; color: #166534; }
        .status-dp { background: #f3e8ff; color: #7e22ce; }
        .status-selesai { background: #dbeafe; color: #1e40af; }

        .grand-total-box {
            background: #e0e7ff;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .grand-total-label {
            font-size: 12px;
            font-weight: 700;
            color: #3730a3;
            text-transform: uppercase;
        }

        .grand-total-val {
            font-size: 20px;
            font-weight: 800;
            color: #3730a3;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
        }

        .note-text {
            font-size: 11px;
            color: #94a3b8;
            max-width: 320px;
            font-style: italic;
        }

        .sign-box {
            text-align: center;
            font-size: 11px;
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
            body { padding: 0; background: #ffffff; }
            .receipt-card { box-shadow: none; border: none; padding: 0; }
        }
    </style>
</head>
<body>

    <button type="button" class="btn-print-floating" onclick="window.print()">
        <i class="fa-solid fa-print"></i> Cetak / Simpan Nota PDF
    </button>

    <div class="receipt-card">
        <table class="header-table">
            <tr>
                <td>
                    <div class="homestay-title">{{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</div>
                    <div class="homestay-meta">
                        {{ $setting->address ?? 'Banjarbaru, Kalimantan Selatan' }}<br>
                        No. WA / Kontak: +{{ preg_replace('/[^0-9]/', '', $setting->wa_number ?? '6281234567890') }}
                    </div>
                </td>
                <td style="text-align: right;">
                    <div class="receipt-badge-title">NOTA PEMBAYARAN</div>
                    <div class="receipt-code-display">#{{ $booking->booking_code }}</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        Tgl: {{ date('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Guest & Booking Info Grid -->
        <div class="grid-meta">
            <div class="meta-box">
                <h4>Informasi Tamu</h4>
                <p>{{ $booking->customer_name }}</p>
                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                    <i class="fa-solid fa-phone"></i> {{ $booking->customer_phone }}<br>
                    <i class="fa-solid fa-location-dot"></i> {{ $booking->customer_address }}
                </div>
            </div>
            <div class="meta-box">
                <h4>Detail Reservasi</h4>
                <p>{{ $booking->room->name ?? 'Kamar' }} ({{ $booking->room->code ?? '' }})</p>
                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                    <i class="fa-solid fa-calendar-day"></i> Check-in: {{ $booking->check_in_date->format('d M Y') }}<br>
                    <i class="fa-solid fa-calendar-check"></i> Check-out: {{ $booking->check_out_date->format('d M Y') }} ({{ $booking->total_nights }} Malam)
                </div>
            </div>
        </div>

        <!-- Breakdown Table -->
        <table class="table-items">
            <thead>
                <tr>
                    <th>Item Deskripsi</th>
                    <th style="text-align: center;">Durasi / Qty</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $details = $booking->room ? $booking->room->calculateBookingDetails($booking->check_in_date, $booking->check_out_date) : null;
                    $weekdayNights = $details['weekday_nights'] ?? $booking->total_nights;
                    $weekendNights = $details['weekend_nights'] ?? 0;

                    $effectiveDiscount = ($booking->admin_discount && $booking->admin_discount > 0) ? $booking->admin_discount : ($booking->discount ?? 0);
                    $multiplier = 1 - ($effectiveDiscount / 100);

                    $baseWeekday = $booking->room ? $booking->room->price : $booking->room_price;
                    $baseWeekend = ($booking->room && $booking->room->weekend_price > 0) ? $booking->room->weekend_price : $baseWeekday;

                    $netWeekdayUnit = $baseWeekday * $multiplier;
                    $netWeekendUnit = $baseWeekend * $multiplier;

                    $weekdaySubtotal = $netWeekdayUnit * $weekdayNights;
                    $weekendSubtotal = $netWeekendUnit * $weekendNights;
                @endphp

                @if($weekdayNights > 0)
                <tr>
                    <td>
                        <strong>Sewa {{ $booking->room->name ?? 'Kamar' }} (Hari Biasa / Weekday)</strong>
                        @if($booking->admin_discount && $booking->admin_discount > 0)
                            <div style="font-size: 10px; color: #ea580c; font-weight: bold;">Diskon Khusus Admin {{ number_format($booking->admin_discount, 0) }}% OFF</div>
                        @elseif($booking->discount && $booking->discount > 0)
                            <div style="font-size: 10px; color: #dc2626;">Diskon {{ number_format($booking->discount, 0) }}% OFF</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $weekdayNights }} Malam</td>
                    <td style="text-align: right;">Rp {{ number_format($netWeekdayUnit, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($weekdaySubtotal, 0, ',', '.') }}</td>
                </tr>
                @endif

                @if($weekendNights > 0)
                <tr>
                    <td>
                        <strong>Sewa {{ $booking->room->name ?? 'Kamar' }} (Akhir Pekan / Weekend)</strong>
                        @if($booking->admin_discount && $booking->admin_discount > 0)
                            <div style="font-size: 10px; color: #ea580c; font-weight: bold;">Diskon Khusus Admin {{ number_format($booking->admin_discount, 0) }}% OFF</div>
                        @elseif($booking->discount && $booking->discount > 0)
                            <div style="font-size: 10px; color: #dc2626;">Diskon {{ number_format($booking->discount, 0) }}% OFF</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $weekendNights }} Malam</td>
                    <td style="text-align: right;">Rp {{ number_format($netWeekendUnit, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($weekendSubtotal, 0, ',', '.') }}</td>
                </tr>
                @endif

                @if(is_array($booking->extra_facilities) && count($booking->extra_facilities) > 0)
                    @foreach($booking->extra_facilities as $ef)
                        <tr>
                            <td>
                                <strong>Extra: {{ $ef['name'] ?? 'Fasilitas Tambahan' }}</strong>
                            </td>
                            <td style="text-align: center;">1 Item</td>
                            <td style="text-align: right;">Rp {{ number_format($ef['price'] ?? 0, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: bold;">Rp {{ number_format($ef['price'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Grand Total & Status -->
        <div class="grand-total-box">
            <div>
                <div class="grand-total-label">Status Pembayaran</div>
                <div style="margin-top: 4px;">
                    @if(in_array($booking->status, [2]))
                        <span class="status-pill status-lunas"><i class="fa-solid fa-circle-check"></i> LUNAS</span>
                    @elseif($booking->status == 3)
                        <span class="status-pill status-selesai"><i class="fa-solid fa-flag-checkered"></i> SELESAI</span>
                    @elseif($booking->status == 4)
                        <span class="status-pill status-dp"><i class="fa-solid fa-coins"></i> DP 50%</span>
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div class="grand-total-label">Total Pembayaran</div>
                <div class="grand-total-val">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <div class="note-text">
                * Terima kasih telah menginap di {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}. Nota ini merupakan bukti pembayaran yang sah.
            </div>
            <div class="sign-box">
                Kotabaru, {{ date('d F Y') }}<br>
                <strong>Pengelola / Admin Glamping</strong>
                <br><br><br><br>
                ( {{ auth()->user()->name ?? 'Admin Gedambaan Glamping' }} )
            </div>
        </div>
    </div>

</body>
</html>
