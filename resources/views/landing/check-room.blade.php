@php
    $setting = $setting ?? \App\Models\Setting::getSetting();
    $rawWa = $setting->wa_number ?? '';
    $waClean = preg_replace('/[^0-9]/', '', $rawWa);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    }
    $calMonth = sprintf('%02d', (int) ($calMonth ?? date('m')));
    $calYear = (int) ($calYear ?? date('Y'));
    $firstDay = \Carbon\Carbon::createFromDate((int)$calYear, (int)$calMonth, 1);
    $daysInMonth = $firstDay->daysInMonth;
    $startOffset = $firstDay->dayOfWeekIso - 1; // 1 = Monday (0 offset), 7 = Sunday (6 offset)
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Ketersediaan Unit Glamping - {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</title>
    <meta name="description" content="Kalender ketersediaan unit glamping real-time di {{ $setting->homestay_name ?? 'Gedambaan Glamping' }}. Cek tanggal bebas dan pesan unit favorit Anda.">
    
    <!-- Dynamic Favicon -->
    @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
        <link rel="icon" type="image/png" href="/{{ ltrim($setting->logo, '/') }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏠</text></svg>">
    @endif

    <style>
        :root {
            --nusakos-bg: #f7f4ef;
            --nusakos-card-bg: #ffffff;
            --nusakos-brown: #8c7355;
            --nusakos-brown-dark: #6e583e;
            --nusakos-text-dark: #3a3229;
            --nusakos-text-muted: #82786c;
            --nusakos-border: #ede7de;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--nusakos-bg);
            color: var(--nusakos-text-dark);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Top Header Navigation */
        .nusakos-navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid var(--nusakos-border);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .nusakos-nav-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--nusakos-text-dark);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .btn-back-link:hover {
            color: var(--nusakos-brown);
        }

        .brand-logo-text {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: var(--nusakos-text-dark);
            font-weight: 800;
            font-size: 1.15rem;
        }

        .brand-logo-img {
            height: 38px;
            width: auto;
            border-radius: 8px;
        }

        .btn-contact-header {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: 50px;
            border: 1.5px solid var(--nusakos-text-dark);
            background: transparent;
            color: var(--nusakos-text-dark);
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-contact-header:hover {
            background: var(--nusakos-text-dark);
            color: #ffffff;
        }

        /* Container Main */
        .check-room-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem 1.25rem;
        }

        /* Page Hero Header Box (Matching Warm Brown Nusakos Theme) */
        .page-hero-card {
            background: linear-gradient(135deg, var(--nusakos-brown), var(--nusakos-brown-dark));
            color: #ffffff;
            border-radius: 24px;
            padding: 2rem 2.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(140, 115, 85, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-hero-title {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-hero-desc {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.85);
            max-width: 620px;
            line-height: 1.5;
        }

        /* Filter Selector Form */
        .filter-form-box {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 0.75rem 1rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-select {
            background: #ffffff;
            color: var(--nusakos-text-dark);
            border: none;
            padding: 0.5rem 0.85rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.875rem;
            outline: none;
            cursor: pointer;
        }

        /* Room Cards Grid */
        .room-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(330px, 1fr));
            gap: 1.75rem;
        }

        .room-detail-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid var(--nusakos-border);
            box-shadow: 0 6px 20px rgba(0,0,0,0.04);
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .room-detail-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        .badge-code {
            background-color: #f4eee5;
            color: var(--nusakos-brown);
            border: 1px solid #e5dace;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 800;
            font-size: 0.8rem;
        }

        .facility-badge-pill {
            background-color: #f4eee5;
            color: #5c4e3e;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-size: 0.73rem;
            display: inline-block;
            margin: 0.15rem 0.1rem;
        }

        /* Legend Dots (Matching booking.blade.php calendar screenshot) */
        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
        }

        /* Room Calendar Grid (Exact styling matching booking page screenshot) */
        .room-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 0.75rem;
            text-align: center;
        }

        .calendar-day-header {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--nusakos-text-muted);
            padding: 0.25rem 0;
        }

        .calendar-day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            position: relative;
        }

        .day-available {
            background-color: #ffffff;
            color: var(--nusakos-text-dark);
            border: 1px solid var(--nusakos-border);
        }

        .day-weekend {
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #c084fc;
            font-weight: 700;
        }

        .day-booked {
            background-color: #d8c4b0;
            color: #ffffff;
            font-weight: 700;
            border: none;
            cursor: not-allowed;
        }

        .day-maintenance {
            background-color: #ffedd5;
            color: #ea580c;
            border: 1px solid #fed7aa;
            font-weight: 700;
            cursor: not-allowed;
        }

        .btn-pesan-room {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            background: linear-gradient(135deg, var(--nusakos-brown), var(--nusakos-brown-dark));
            color: #ffffff;
            text-decoration: none;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.875rem;
            margin-top: 1.25rem;
            box-shadow: 0 4px 14px rgba(140, 115, 85, 0.25);
            transition: all 0.2s ease;
        }

        .btn-pesan-room:hover {
            background: linear-gradient(135deg, #7c6347, #5c4731);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(140, 115, 85, 0.35);
            color: #ffffff;
        }

        /* Footer */
        .landing-footer {
            background: #ffffff;
            border-top: 1px solid var(--nusakos-border);
            padding: 2rem 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--nusakos-text-muted);
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="nusakos-navbar">
        <div class="nusakos-nav-left">
            <a href="{{ route('home') }}" class="btn-back-link">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('home') }}" class="brand-logo-text">
                @if(!empty($setting->logo) && file_exists(public_path(ltrim($setting->logo, '/'))))
                    <img src="/{{ ltrim($setting->logo, '/') }}" alt="Logo {{ $setting->homestay_name }}" class="brand-logo-img">
                @endif
                <span>{{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</span>
            </a>
        </div>

        <div>
            <a href="https://wa.me/{{ $waClean }}?text=Halo%20Admin%20{{ urlencode($setting->homestay_name ?? 'Gedambaan Glamping') }},%20saya%20ingin%20bertanya%20informasi%20ketersediaan." target="_blank" class="btn-contact-header">
                <i class="fa-solid fa-phone"></i>
                <span>Hubungi Kami</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="check-room-container">
        
        <!-- Hero Header Box -->
        <div class="page-hero-card">
            <div>
                <div class="page-hero-title">
                    <i class="fa-solid fa-calendar-days" style="color: #fef08a;"></i>
                    <span>Kalender Ketersediaan Kamar & Unit</span>
                </div>
                <div class="page-hero-desc">
                    Pantau tanggal ketersediaan kamar secara real-time. Pilih kamar favorit Anda lalu klik <strong>Pesan Kamar Ini</strong> untuk reservasi instant.
                </div>
            </div>

            <!-- Month & Year Filter Form -->
            <form action="{{ route('check-room') }}" method="GET" class="filter-form-box">
                <i class="fa-regular fa-calendar-check" style="color: #ffffff; font-size: 1.1rem;"></i>
                <select name="cal_month" class="filter-select" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        @php $mStr = sprintf('%02d', $m); @endphp
                        <option value="{{ $mStr }}" {{ $mStr == $calMonth ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="cal_year" class="filter-select" onchange="this.form.submit()">
                    @for($y = date('Y'); $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $y == $calYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <!-- Room Cards Grid -->
        <div class="room-cards-grid">
            @foreach($rooms as $room)
                @php
                    // Build map of booked dates 'YYYY-MM-DD' => boolean
                    $bookedMap = [];
                    foreach($room->bookings as $b) {
                        $checkIn = \Carbon\Carbon::parse($b->check_in_date);
                        $checkOut = \Carbon\Carbon::parse($b->check_out_date);

                        for ($dt = $checkIn->copy(); $dt->lt($checkOut); $dt->addDay()) {
                            $bookedMap[$dt->format('Y-m-d')] = true;
                        }
                    }

                    // Build map of maintenance dates 'YYYY-MM-DD' => note
                    $maintenanceMap = [];
                    foreach($room->maintenances as $m) {
                        $mStart = \Carbon\Carbon::parse($m->start_date);
                        $mEnd = \Carbon\Carbon::parse($m->end_date);
                        for ($dt = $mStart->copy(); $dt->lte($mEnd); $dt->addDay()) {
                            $maintenanceMap[$dt->format('Y-m-d')] = $m->note ?? 'Pemeliharaan / Maintenance';
                        }
                    }

                    $imgs = is_array($room->images) ? array_values(array_filter($room->images)) : [];
                    $validImgs = [];
                    foreach ($imgs as $im) {
                        if (!empty($im)) {
                            $validImgs[] = str_starts_with($im, 'http') ? $im : ('/' . ltrim($im, '/'));
                        }
                    }
                    $thumb = count($validImgs) > 0 ? end($validImgs) : null;
                @endphp

                <div class="room-detail-card">
                    <div>
                        <!-- Header Card -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.85rem;">
                            <div>
                                <span class="badge-code">{{ $room->code }}</span>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-top: 0.35rem;">{{ $room->name }}</h3>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.05rem; font-weight: 800; color: var(--nusakos-brown);">Rp {{ number_format($room->final_price, 0, ',', '.') }}</div>
                                <div style="font-size: 0.7rem; color: var(--nusakos-text-muted);">/ malam (Weekday)</div>
                                @if($room->weekend_price && $room->weekend_price > 0)
                                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--nusakos-brown-dark); margin-top: 0.15rem;">
                                        Weekend: Rp {{ number_format($room->final_weekend_price, 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Image & Facilities Overview -->
                        <div style="display: flex; gap: 0.85rem; margin-bottom: 1rem; align-items: center;">
                            <div style="width: 75px; height: 65px; border-radius: 12px; overflow: hidden; background-color: #f1f5f9; flex-shrink: 0;">
                                @if($thumb)
                                    <img src="{{ $thumb }}" alt="{{ $room->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; color: #cbd5e1;">
                                        <i class="fa-solid fa-bed" style="font-size: 1.35rem;"></i>
                                    </div>
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                        <i class="fa-solid fa-bed" style="font-size: 1.35rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="font-size: 0.72rem; color: var(--nusakos-text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 0.2rem;">Fasilitas Utama</div>
                                <div>
                                    @forelse($room->facilities->take(3) as $f)
                                        <span class="facility-badge-pill">
                                            <i class="fa-solid {{ $f->icon ?? 'fa-check' }}" style="font-size: 0.65rem; color: var(--nusakos-brown);"></i> {{ $f->name }}
                                        </span>
                                    @empty
                                        <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Tanpa fasilitas</span>
                                    @endforelse
                                    @if($room->facilities->count() > 3)
                                        <span style="font-size: 0.7rem; color: var(--nusakos-brown); font-weight: 700;">+{{ $room->facilities->count() - 3 }} lainnya</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Section -->
                    <div style="border-top: 1px solid var(--nusakos-border); padding-top: 0.85rem; margin-top: 0.5rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                            <span style="font-size: 0.78rem; font-weight: 800; color: #334155;">Kalender {{ $firstDay->translatedFormat('F Y') }}</span>
                            <!-- Legend Matching Screenshot -->
                            <div style="display: flex; gap: 0.65rem; font-size: 0.68rem; font-weight: 600; color: var(--nusakos-text-muted); flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 0.25rem;"><span class="legend-dot" style="background: #ffffff; border: 1px solid #d1d5db;"></span> Weekday</div>
                                <div style="display: flex; align-items: center; gap: 0.25rem;"><span class="legend-dot" style="background: #f3e8ff; border: 1px solid #c084fc;"></span> Weekend/Libur</div>
                                <div style="display: flex; align-items: center; gap: 0.25rem;"><span class="legend-dot" style="background: #ffedd5; border: 1px solid #fed7aa;"></span> Maintenance</div>
                                <div style="display: flex; align-items: center; gap: 0.25rem;"><span class="legend-dot" style="background: #d8c4b0;"></span> Terisi</div>
                            </div>
                        </div>

                        <!-- Calendar Grid (Monday to Sunday Order) -->
                        <div class="room-calendar-grid">
                            <!-- Day Headers (Senin - Minggu) -->
                            <div class="calendar-day-header">Sen</div>
                            <div class="calendar-day-header">Sel</div>
                            <div class="calendar-day-header">Rab</div>
                            <div class="calendar-day-header">Kam</div>
                            <div class="calendar-day-header">Jum</div>
                            <div class="calendar-day-header">Sab</div>
                            <div class="calendar-day-header">Min</div>

                            <!-- Empty offset days -->
                            @for($i = 0; $i < $startOffset; $i++)
                                <div class="calendar-day-cell" style="background: transparent;"></div>
                            @endfor

                             <!-- Days of Month -->
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dStr = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $d);
                                    $isBooked = isset($bookedMap[$dStr]);
                                    $isMaintenance = isset($maintenanceMap[$dStr]);
                                    $maintNote = $isMaintenance ? $maintenanceMap[$dStr] : null;

                                    $dtCarbon = \Carbon\Carbon::createFromDate((int)$calYear, (int)$calMonth, $d);
                                    $dayOfWeek = $dtCarbon->dayOfWeek; // 5 = Fri, 6 = Sat
                                    $isWeekendOrHoliday = in_array($dayOfWeek, [5, 6]) || in_array($dStr, $holidayDates ?? []);
                                @endphp

                                @if($isBooked)
                                    <div class="calendar-day-cell day-booked" title="Terbooking">
                                        {{ $d }}
                                    </div>
                                @elseif($isMaintenance)
                                    <div class="calendar-day-cell day-maintenance" title="Maintenance: {{ $maintNote }}">
                                        {{ $d }}
                                    </div>
                                @elseif($isWeekendOrHoliday)
                                    <div class="calendar-day-cell day-weekend" title="Rate Weekend / Tanggal Merah">
                                        {{ $d }}
                                    </div>
                                @else
                                    <div class="calendar-day-cell day-available" title="Kamar Bebas (Weekday)">
                                        {{ $d }}
                                    </div>
                                @endif
                            @endfor
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('booking', $room->id) }}" class="btn-pesan-room">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Pesan Kamar Ini</span>
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <footer class="landing-footer">
        <div style="max-width: 1240px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                &copy; {{ date('Y') }} <strong>{{ $setting->homestay_name ?? 'Gedambaan Glamping' }}</strong>. All Rights Reserved.
            </div>
            <div>
                <a href="https://wa.me/{{ $waClean }}" target="_blank" style="color: var(--nusakos-brown); text-decoration: none; font-weight: 700;">
                    <i class="fa-brands fa-whatsapp"></i> Hubungi Admin
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
