@extends('admin.layouts.app')

@section('title', 'Detail Kamar & Kalender')
@section('page_title', 'Detail Kamar & Kalender Ketersediaan Booking')

@section('styles')
<style>
    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s ease;
    }

    .modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-card {
        background: #ffffff;
        width: 100%;
        max-width: 620px;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(20px);
        transition: transform 0.25s ease;
    }

    .modal-backdrop.show .modal-card {
        transform: translateY(0);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }

    .modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }

    .btn-close-modal {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Room Detail & Calendar Cards Grid */
    .room-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 1.25rem;
    }

    .room-detail-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .room-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-top: 0.75rem;
        text-align: center;
    }

    .calendar-day-header {
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        padding: 0.2rem 0;
        text-transform: uppercase;
    }

    .calendar-day-cell {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        position: relative;
    }

    .day-available {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .day-weekend {
        background-color: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #c084fc;
        font-weight: 800;
    }

    .day-booked {
        background-color: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
        font-weight: 800;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .day-booked:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(220, 38, 38, 0.2);
        z-index: 10;
    }

    .day-booked::before,
    .day-booked::after {
        content: '';
        position: absolute;
        width: 2px;
        height: 110%;
        background-color: #dc2626;
        top: -5%;
        left: 50%;
        opacity: 0.8;
        pointer-events: none;
    }

    .day-booked::before {
        transform: rotate(45deg);
    }

    .day-booked::after {
        transform: rotate(-45deg);
    }

    .day-maintenance {
        background-color: #ffedd5;
        color: #ea580c;
        border: 1px solid #fed7aa;
        font-weight: 800;
        position: relative;
    }

    .badge-code {
        background-color: #e0e7ff;
        color: #4338ca;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        font-family: monospace;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .facility-badge-pill {
        background-color: #f1f5f9;
        color: #475569;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        font-size: 0.75rem;
        display: inline-block;
        margin: 0.15rem 0.1rem;
    }
</style>
@endsection

@section('content')

<!-- Card Utama Detail Kamar & Kalender -->
<div class="card" style="width: 100%;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div>
            <h2 class="card-title" style="margin-bottom: 0;">
                <i class="fa-solid fa-calendar-days" style="color: #4f46e5;"></i>
                Detail Kamar & Kalender Ketersediaan Booking
            </h2>
            <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                Tanggal terbooking ditandai <span style="color: #dc2626; font-weight: 700;">(X) Merah</span>. Hari <span style="color: #7e22ce; font-weight: 700;">Weekend & Tgl Merah</span> ditandai <span style="color: #7e22ce; font-weight: 700;">Ungu</span>. Status <span style="color: #ea580c; font-weight: 700;">Maintenance (MT)</span> ditandai <span style="color: #ea580c; font-weight: 700;">Oranye</span>. <strong>Klik tanggal merah</strong> untuk membuka detail pemesanan.
            </div>
        </div>

        @php
            $calMonth = request('cal_month', date('m'));
            $calYear = request('cal_year', date('Y'));
            $firstDay = \Carbon\Carbon::createFromDate((int)$calYear, (int)$calMonth, 1);
            $daysInMonth = $firstDay->daysInMonth;
            $startOffset = $firstDay->dayOfWeek; // 0 = Sunday
        @endphp

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <button type="button" class="btn-submit" style="background-color: #7e22ce; width: auto; padding: 0.45rem 0.95rem; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(126, 34, 206, 0.3);" onclick="openHolidayModal()">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Kelola Libur / Rate Weekend Khusus</span>
            </button>

            <!-- Month Filter Form -->
            <form action="{{ route('admin.rooms.details') }}" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
                <select name="cal_month" class="form-select" style="width: auto; padding: 0.4rem 0.85rem; border-radius: 8px; font-weight: 600;" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        @php $mStr = sprintf('%02d', $m); @endphp
                        <option value="{{ $mStr }}" {{ $mStr == $calMonth ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }} {{ $calYear }}
                        </option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    <!-- Grid Cards Kamar -->
    <div class="room-cards-grid">
        @foreach($rooms as $room)
            @php
                // Build map of booked dates 'YYYY-MM-DD' => booking info
                $bookedMap = [];
                foreach($room->bookings as $b) {
                    $checkIn = \Carbon\Carbon::parse($b->check_in_date);
                    $checkOut = \Carbon\Carbon::parse($b->check_out_date);

                    for ($dt = $checkIn->copy(); $dt->lt($checkOut); $dt->addDay()) {
                        $bookedMap[$dt->format('Y-m-d')] = [
                            'id' => $b->id,
                            'customer' => $b->customer_name,
                            'code' => $b->booking_code,
                        ];
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
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-top: 0.35rem;">{{ $room->name }}</h3>
                            <button type="button" class="btn-submit" style="background-color: #ea580c; width: auto; padding: 0.25rem 0.6rem; font-size: 0.72rem; margin-top: 0.35rem; border-radius: 6px; box-shadow: 0 2px 6px rgba(234, 88, 12, 0.25);" onclick="openMaintenanceModal({{ $room->id }}, '{{ addslashes($room->name) }}')">
                                <i class="fa-solid fa-wrench"></i> Maintenance Kamar
                            </button>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1rem; font-weight: 800; color: #16a34a;">Rp {{ number_format($room->final_price, 0, ',', '.') }}</div>
                            <div style="font-size: 0.7rem; color: #64748b;">/ malam</div>
                        </div>
                    </div>

                    <!-- Image & Facilities Overview -->
                    <div style="display: flex; gap: 0.85rem; margin-bottom: 1rem; align-items: center;">
                        <div style="width: 70px; height: 60px; border-radius: 10px; overflow: hidden; background-color: #f1f5f9; flex-shrink: 0;">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $room->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; color: #cbd5e1;">
                                    <i class="fa-solid fa-bed" style="font-size: 1.25rem;"></i>
                                </div>
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                    <i class="fa-solid fa-bed" style="font-size: 1.25rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div style="flex-grow: 1;">
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 0.2rem;">Fasilitas Utama</div>
                            <div>
                                @forelse($room->facilities->take(3) as $f)
                                    <span class="facility-badge-pill" style="font-size: 0.7rem; padding: 0.1rem 0.35rem;">
                                        <i class="fa-solid {{ $f->icon ?? 'fa-check' }}" style="font-size: 0.65rem; color: #4f46e5;"></i> {{ $f->name }}
                                    </span>
                                @empty
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Tanpa fasilitas</span>
                                @endforelse
                                @if($room->facilities->count() > 3)
                                    <span style="font-size: 0.7rem; color: #4f46e5; font-weight: 600;">+{{ $room->facilities->count() - 3 }} lainnya</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 0.85rem; margin-top: 0.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #334155;">Kalender {{ $firstDay->translatedFormat('F Y') }}</span>
                        <div style="display: flex; gap: 0.4rem; font-size: 0.65rem; font-weight: 600; flex-wrap: wrap;">
                            <span style="color: #16a34a;">🟢 Bebas</span>
                            <span style="color: #7e22ce;">🟣 Weekend/Libur</span>
                            <span style="color: #ea580c;">🟠 Maintenance</span>
                            <span style="color: #dc2626;">🔴 X Terbooking</span>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="room-calendar-grid">
                        <!-- Day Headers -->
                        <div class="calendar-day-header">Min</div>
                        <div class="calendar-day-header">Sen</div>
                        <div class="calendar-day-header">Sel</div>
                        <div class="calendar-day-header">Rab</div>
                        <div class="calendar-day-header">Kam</div>
                        <div class="calendar-day-header">Jum</div>
                        <div class="calendar-day-header">Sab</div>

                        <!-- Empty offset days -->
                        @for($i = 0; $i < $startOffset; $i++)
                            <div class="calendar-day-cell" style="background: transparent;"></div>
                        @endfor

                        <!-- Days of Month -->
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dStr = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $d);
                                $isBooked = isset($bookedMap[$dStr]);
                                $bookInfo = $isBooked ? $bookedMap[$dStr] : null;

                                $isMaintenance = isset($maintenanceMap[$dStr]);
                                $maintNote = $isMaintenance ? $maintenanceMap[$dStr] : null;

                                $dtCarbon = \Carbon\Carbon::createFromDate((int)$calYear, (int)$calMonth, $d);
                                $dayOfWeek = $dtCarbon->dayOfWeek; // 5 = Fri, 6 = Sat
                                $isWeekendOrHoliday = in_array($dayOfWeek, [5, 6]) || in_array($dStr, $holidayDates ?? []);
                            @endphp

                            @if($isBooked)
                                <a href="{{ route('admin.bookings.index') }}?code={{ $bookInfo['code'] }}" class="calendar-day-cell day-booked" style="text-decoration: none;" title="Terbooking oleh: {{ $bookInfo['customer'] }} ({{ $bookInfo['code'] }}) - Klik untuk lihat detail pemesanan">
                                    {{ $d }}
                                </a>
                            @elseif($isMaintenance)
                                <div class="calendar-day-cell day-maintenance" title="Maintenance / Perbaikan: {{ $maintNote }}">
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
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Kelola Tanggal Libur / Rate Weekend Khusus -->
<div class="modal-backdrop" id="holidayModal">
    <div class="modal-card" style="max-width: 620px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fa-solid fa-calendar-star" style="color: #7e22ce;"></i> Kelola Tanggal Libur & Rate Weekend Khusus</h3>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.2rem;">Tambah tanggal khusus agar otomatis memakai Harga Weekend & Libur (berwarna Ungu di kalender).</div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeHolidayModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Tambah Tanggal Khusus -->
        <form action="{{ route('admin.holidays.store') }}" method="POST" style="background: #f8fafc; padding: 1.1rem; border-radius: 14px; border: 1px solid #e2e8f0; margin-bottom: 1.25rem;">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="holiday_date" class="form-label">Tanggal Khusus / Libur <span style="color: #dc2626;">*</span></label>
                    <input type="date" id="holiday_date" name="date" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="holiday_name" class="form-label">Keterangan Tanggal <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="holiday_name" name="name" class="form-input" placeholder="misal: Cuti Bersama / High Season" required>
                </div>
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn-submit" style="background-color: #7e22ce; width: auto; padding: 0.45rem 1rem; font-size: 0.8rem;">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Tambah Tanggal</span>
                </button>
            </div>
        </form>

        <!-- Daftar Tanggal Merah / Khusus -->
        <div>
            <label class="form-label" style="margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                <span>Daftar Tanggal Khusus Terdaftar</span>
                <span style="font-weight: 400; font-size: 0.75rem; color: #64748b;">({{ count($allHolidays ?? []) }} Tanggal)</span>
            </label>
            <div style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
                <table class="data-table" style="font-size: 0.8rem;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th style="text-align: center; width: 60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allHolidays ?? [] as $h)
                        <tr>
                            <td style="font-weight: 700; color: #7e22ce;">{{ \Carbon\Carbon::parse($h->date)->format('d M Y') }} ({{ \Carbon\Carbon::parse($h->date)->translatedFormat('l') }})</td>
                            <td>{{ $h->name }}</td>
                            <td style="text-align: center;">
                                <form action="{{ route('admin.holidays.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Hapus tanggal libur ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.25rem 0.5rem; border-radius: 6px; cursor: pointer;" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">Belum ada tanggal khusus terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
            <button type="button" class="btn-edit" style="background-color: #f1f5f9; color: #475569; width: auto; padding: 0.5rem 1.2rem;" onclick="closeHolidayModal()">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Maintenance Kamar -->
<div class="modal-backdrop" id="maintenanceModal">
    <div class="modal-card" style="max-width: 620px;">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fa-solid fa-wrench" style="color: #ea580c;"></i> Kelola Maintenance Kamar</h3>
                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.2rem;">
                    Kamar: <strong id="maint_room_name" style="color: #0f172a;"></strong>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeMaintenanceModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Form Tambah Maintenance Kamar -->
        <form action="{{ route('admin.room-maintenances.store') }}" method="POST" style="background: #fff7ed; padding: 1.1rem; border-radius: 14px; border: 1px solid #ffedd5; margin-bottom: 1.25rem;">
            @csrf
            <input type="hidden" name="room_id" id="maint_room_id">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="maint_start_date" class="form-label">Tgl Mulai MT <span style="color: #dc2626;">*</span></label>
                    <input type="date" id="maint_start_date" name="start_date" class="form-input" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="maint_end_date" class="form-label">Tgl Selesai MT <span style="color: #dc2626;">*</span></label>
                    <input type="date" id="maint_end_date" name="end_date" class="form-input" required>
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label for="maint_note" class="form-label">Keterangan / Alasan Perbaikan</label>
                <input type="text" id="maint_note" name="note" class="form-input" placeholder="misal: Perbaikan AC, Cat ulang dinding, dsb.">
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn-submit" style="background-color: #ea580c; width: auto; padding: 0.45rem 1rem; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Simpan Jadwal Maintenance</span>
                </button>
            </div>
        </form>

        <!-- Daftar Maintenance Terdaftar -->
        <div>
            <label class="form-label" style="margin-bottom: 0.5rem; display: block;">
                <span>Daftar Maintenance Terdaftar Kamar Ini</span>
            </label>
            <div style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
                <table class="data-table" style="font-size: 0.8rem;">
                    <thead>
                        <tr>
                            <th>Rentang Tanggal MT</th>
                            <th>Keterangan</th>
                            <th style="text-align: center; width: 60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                            @foreach($room->maintenances as $m)
                                <tr class="maint-row maint-room-{{ $room->id }}" style="display: none;">
                                    <td style="font-weight: 700; color: #ea580c;">
                                        {{ \Carbon\Carbon::parse($m->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($m->end_date)->format('d M Y') }}
                                    </td>
                                    <td>{{ $m->note ?? 'Perbaikan Kamar' }}</td>
                                    <td style="text-align: center;">
                                        <form action="{{ route('admin.room-maintenances.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal maintenance ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.25rem 0.5rem; border-radius: 6px; cursor: pointer;" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        <tr class="maint-empty" style="display: none;">
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">Belum ada jadwal maintenance terdaftar untuk kamar ini.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 1.25rem;">
            <button type="button" class="btn-edit" style="background-color: #f1f5f9; color: #475569; width: auto; padding: 0.5rem 1.2rem;" onclick="closeMaintenanceModal()">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openHolidayModal() {
        document.getElementById('holidayModal').classList.add('show');
    }
    function closeHolidayModal() {
        document.getElementById('holidayModal').classList.remove('show');
    }

    function openMaintenanceModal(roomId, roomName) {
        document.getElementById('maint_room_id').value = roomId;
        document.getElementById('maint_room_name').innerText = roomName;
        
        // Hide all maintenance rows
        document.querySelectorAll('.maint-row').forEach(el => el.style.display = 'none');
        
        // Show rows for selected room
        const roomRows = document.querySelectorAll('.maint-room-' + roomId);
        if (roomRows.length > 0) {
            roomRows.forEach(el => el.style.display = 'table-row');
            const emptyRow = document.querySelector('.maint-empty');
            if (emptyRow) emptyRow.style.display = 'none';
        } else {
            const emptyRow = document.querySelector('.maint-empty');
            if (emptyRow) emptyRow.style.display = 'table-row';
        }
        
        document.getElementById('maintenanceModal').classList.add('show');
    }
    function closeMaintenanceModal() {
        document.getElementById('maintenanceModal').classList.remove('show');
    }
</script>
@endsection
