@extends('admin.layouts.app')

@section('title', 'Pemesanan Kamar')
@section('page_title', 'Pengelolaan Pemesanan Homestay')

@section('styles')
<style>
    /* Checkbox Group for Extra Facilities */
    .facility-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.6rem;
        background: #f8fafc;
        padding: 0.875rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        max-height: 180px;
        overflow-y: auto;
    }

    .facility-checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.825rem;
        color: #334155;
        cursor: pointer;
    }

    .facility-checkbox-item input[type="checkbox"] {
        accent-color: #4f46e5;
        width: 16px;
        height: 16px;
        cursor: pointer;
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

    .badge-discount-percent {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .status-pill {
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .status-pill.status-pending {
        background-color: #fef3c7;
        color: #b45309;
    }

    .status-pill.status-lunas {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-pill.status-batal {
        background-color: #fee2e2;
        color: #991b1b;
    }

    /* Action Buttons Vertical Layout */
    .action-btns {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.35rem;
        width: 100%;
        max-width: 100px;
        margin: 0 auto;
    }

    /* Custom Pagination Styling */
    .pagination {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .pagination li a,
    .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 0.5rem;
        border-radius: 8px;
        font-size: 0.825rem;
        font-weight: 600;
        text-decoration: none;
        color: #475569;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }

    .pagination li.active span {
        background-color: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }

    .pagination li a:hover {
        background-color: #e0e7ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }

    .pagination li.disabled span {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .action-btns form {
        width: 100%;
        margin: 0;
    }

    .btn-lunas {
        background-color: #dcfce7;
        color: #166534;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-lunas:hover {
        background-color: #bbf7d0;
    }

    .btn-preview {
        background-color: #ccfbf1;
        color: #0f766e;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-preview:hover {
        background-color: #99f6e4;
    }

    .btn-edit {
        background-color: #e0e7ff;
        color: #4338ca;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-edit:hover {
        background-color: #c7d2fe;
    }

    .btn-delete {
        background-color: #fee2e2;
        color: #dc2626;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
    }

    .btn-delete:hover {
        background-color: #fca5a5;
    }

    .btn-print-nota {
        background-color: #fef3c7;
        color: #b45309;
        border: none;
        padding: 0.35rem 0.6rem;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        transition: all 0.2s;
        width: 100%;
        text-decoration: none;
    }

    .btn-print-nota:hover {
        background-color: #fde68a;
        color: #78350f;
    }

    /* Modal Backdrop & Card */
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
        max-width: 580px;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        transform: translateY(20px);
        transition: transform 0.25s ease;
        max-height: 90vh;
        overflow-y: auto;
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
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #64748b;
        cursor: pointer;
    }
</style>
@endsection

@section('content')

<!-- Card Full Width Tabel Daftar Pemesanan -->
<div class="card" style="width: 100%;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <h2 class="card-title" style="margin-bottom: 0;">
            <i class="fa-solid fa-list-check" style="color: #4f46e5;"></i>
            Daftar Pemesanan Homestay
        </h2>

        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <!-- Filter & Search Form -->
            <form action="{{ route('admin.bookings.index') }}" method="GET" id="booking-filter-form" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <!-- Date Range Filter -->
                <div style="display: flex; align-items: center; gap: 0.35rem; background: #f8fafc; padding: 0 0.55rem; height: 38px; border-radius: 10px; border: 1px solid #cbd5e1;">
                    <i class="fa-regular fa-calendar" style="color: #4f46e5; font-size: 0.8rem;"></i>
                    <input type="date" name="start_date" value="{{ $startDate ?? request('start_date') }}" class="form-input" style="border: none; background: transparent; padding: 0.2rem 0.25rem; font-size: 0.8rem; width: 122px; height: 32px; box-shadow: none;" onchange="this.form.submit()" title="Dari Tanggal Check-In">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate ?? request('end_date') }}" class="form-input" style="border: none; background: transparent; padding: 0.2rem 0.25rem; font-size: 0.8rem; width: 122px; height: 32px; box-shadow: none;" onchange="this.form.submit()" title="Sampai Tanggal Check-In">
                </div>

                <!-- Status Filter (Select2) -->
                <div style="width: 170px;">
                    <select name="status" id="status_filter" class="form-select select2-status" style="width: 100%;">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Pending (WA)</option>
                        <option value="4" {{ request('status') === '4' ? 'selected' : '' }}>DP 50%</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Lunas</option>
                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Selesai</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div style="position: relative; display: flex; align-items: center;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 0.75rem; color: #94a3b8; font-size: 0.8rem; z-index: 1;"></i>
                    <input type="text" name="search" class="form-input" placeholder="Cari kode, nama, hp..." value="{{ request('search', request('code')) }}" style="padding-left: 2.1rem; padding-right: 2rem; border-radius: 10px; width: 190px; font-size: 0.825rem; height: 38px;">
                    @if(request('search') || request('code') || request('start_date') || request('end_date') || (request()->has('status') && request('status') !== null && request('status') !== ''))
                        <a href="{{ route('admin.bookings.index') }}" style="position: absolute; right: 0.65rem; color: #94a3b8; text-decoration: none; font-size: 0.8rem; z-index: 1;" title="Reset Filter">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-submit" style="padding: 0 0.85rem; height: 38px; border-radius: 10px; background-color: #4f46e5; font-size: 0.825rem;">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>
            </form>

            <button type="button" class="btn-submit" onclick="openCreateBookingModal()" style="height: 38px; border-radius: 10px; font-size: 0.825rem;">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Input Pemesanan Baru</span>
            </button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode & Kamar</th>
                    <th>Pemesan</th>
                    <th>Check-In / Out</th>
                    <th>Status</th>
                    <th>Total Biaya</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $index => $b)
                <tr id="booking_row_{{ $b->booking_code }}" style="{{ request('code') == $b->booking_code ? 'background-color: #e0e7ff; border-left: 4px solid #4f46e5;' : '' }}">
                    <td>{{ $bookings->firstItem() + $index }}</td>
                    <td>
                        <div><span class="badge-code">{{ $b->booking_code }}</span></div>
                        <div style="font-weight: 700; color: #1e293b; margin-top: 0.25rem;">
                            {{ $b->room->name ?? 'Kamar' }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 700; color: #0f172a;">{{ $b->customer_name }}</div>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.1rem;">
                            <i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> {{ $b->customer_phone }}
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #334155; font-size: 0.85rem;">
                            <i class="fa-solid fa-calendar-day" style="color: #4f46e5; font-size: 0.75rem;"></i> In: {{ $b->check_in_date->format('d/m/Y') }}
                        </div>
                        <div style="font-weight: 600; color: #64748b; font-size: 0.85rem; margin-top: 0.15rem;">
                            <i class="fa-solid fa-calendar-check" style="color: #64748b; font-size: 0.75rem;"></i> Out: {{ $b->check_out_date->format('d/m/Y') }} ({{ $b->total_nights }} malam)
                        </div>
                    </td>
                    <td>
                        @php $badge = $b->status_badge; @endphp
                        <span class="status-pill" style="background-color: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                            <i class="fa-solid {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                        </span>
                        @if($b->status == 1 && $b->expired_at)
                            <div style="font-size: 0.72rem; color: #b45309; margin-top: 0.2rem; font-weight: 600;">
                                <i class="fa-solid fa-hourglass-half"></i> Sisa WA: {{ $b->expired_at->diffForHumans(['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @php
                            $extraTotal = 0;
                            if (is_array($b->extra_facilities)) {
                                foreach ($b->extra_facilities as $ef) {
                                    $extraTotal += $ef['price'] ?? 0;
                                }
                            }
                            
                            $details = $b->room ? $b->room->calculateBookingDetails($b->check_in_date, $b->check_out_date) : null;
                            $weekdayNights = $details['weekday_nights'] ?? $b->total_nights;
                            $weekendNights = $details['weekend_nights'] ?? 0;

                            $effectiveDiscount = ($b->admin_discount && $b->admin_discount > 0) ? $b->admin_discount : ($b->discount ?? 0);
                            $multiplier = 1 - ($effectiveDiscount / 100);

                            $baseWeekday = $b->room ? $b->room->price : $b->room_price;
                            $baseWeekend = ($b->room && $b->room->weekend_price > 0) ? $b->room->weekend_price : $baseWeekday;

                            $weekdaySubtotal = ($baseWeekday * $multiplier) * $weekdayNights;
                            $weekendSubtotal = ($baseWeekend * $multiplier) * $weekendNights;

                            $hasBoth = ($weekdayNights > 0 && $weekendNights > 0);
                        @endphp

                        @if($hasBoth)
                            <div style="font-size: 0.75rem; color: #475569;">
                                Weekday: <strong>Rp {{ number_format($weekdaySubtotal, 0, ',', '.') }}</strong> ({{ $weekdayNights }} m)
                            </div>
                            <div style="font-size: 0.75rem; color: #475569;">
                                Weekend: <strong>Rp {{ number_format($weekendSubtotal, 0, ',', '.') }}</strong> ({{ $weekendNights }} m)
                            </div>
                        @elseif($weekendNights > 0)
                            <div style="font-size: 0.78rem; color: #475569;">
                                Kamar Weekend: <strong>Rp {{ number_format($weekendSubtotal, 0, ',', '.') }}</strong> ({{ $weekendNights }} m)
                            </div>
                        @else
                            <div style="font-size: 0.78rem; color: #475569;">
                                Kamar Weekday: <strong>Rp {{ number_format($weekdaySubtotal, 0, ',', '.') }}</strong> ({{ $weekdayNights }} m)
                            </div>
                        @endif

                        @if($extraTotal > 0)
                        <div style="font-size: 0.78rem; color: #4338ca; margin-top: 0.1rem;">
                            Extra: <strong>+Rp {{ number_format($extraTotal, 0, ',', '.') }}</strong>
                        </div>
                        @endif

                        <div style="font-weight: 800; color: #16a34a; font-size: 0.95rem; margin-top: 0.2rem;">
                            Total: Rp {{ number_format($b->total_price, 0, ',', '.') }}
                        </div>

                        @if($b->admin_discount && $b->admin_discount > 0)
                            <div style="margin-top: 0.1rem;"><span class="badge-discount-percent" style="background-color: #ffedd5; color: #c2410c;">Diskon Admin: {{ number_format($b->admin_discount, 0) }}% OFF</span></div>
                        @elseif($b->discount && $b->discount > 0)
                            <div style="margin-top: 0.1rem;"><span class="badge-discount-percent">{{ number_format($b->discount, 0) }}% OFF</span></div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div class="action-btns">
                            @if($b->status == 1)
                            <form action="{{ route('admin.bookings.mark-lunas', $b->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="button" class="btn-lunas" onclick="confirmSubmit(this.form, 'Apakah Anda yakin ingin langsung mengubah status booking {{ $b->booking_code }} menjadi LUNAS?', 'Konfirmasi Pelunasan')">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Lunas</span>
                                </button>
                            </form>
                            @endif

                            @if(in_array($b->status, [2, 3, 4]))
                            <a href="{{ route('admin.bookings.receipt', $b->id) }}" target="_blank" class="btn-print-nota" title="Cetak Nota Pembayaran">
                                <i class="fa-solid fa-print"></i>
                                <span>Nota</span>
                            </a>
                            @endif

                            <button type="button" class="btn-preview" onclick="openPreviewModal({{ json_encode($b) }}, {{ json_encode($b->room) }})">
                                <i class="fa-solid fa-eye"></i>
                                <span>Preview</span>
                            </button>

                            <button type="button" class="btn-edit" onclick="openEditModal({{ json_encode($b) }}, {{ json_encode($b->room) }})">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit</span>
                            </button>

                            <form action="{{ route('admin.bookings.destroy', $b->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-delete" onclick="confirmDelete(this, 'Apakah Anda yakin ingin menghapus pemesanan {{ $b->booking_code }}?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($bookings->hasPages())
    <div style="padding: 1.1rem 1.5rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: #f8fafc; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
        <div style="font-size: 0.825rem; color: #64748b; font-weight: 600;">
            Menampilkan <span style="color: #0f172a; font-weight: 700;">{{ $bookings->firstItem() ?? 0 }}</span> - <span style="color: #0f172a; font-weight: 700;">{{ $bookings->lastItem() ?? 0 }}</span> dari <span style="color: #0f172a; font-weight: 700;">{{ $bookings->total() }}</span> data pemesanan
        </div>

        <div style="display: flex; align-items: center; gap: 0.35rem;">
            {{-- Previous Page Link --}}
            @if ($bookings->onFirstPage())
                <span style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #e2e8f0; color: #94a3b8; font-size: 0.8rem; font-weight: 700; cursor: not-allowed; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i> Prev
                </span>
            @else
                <a href="{{ $bookings->appends(request()->query())->previousPageUrl() }}" style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.7rem;"></i> Prev
                </a>
            @endif

            {{-- Page Numbers --}}
            @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                @php
                    $urlWithQuery = $bookings->appends(request()->query())->url($page);
                @endphp
                @if ($page == $bookings->currentPage())
                    <span style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #4f46e5; color: #ffffff; font-size: 0.8rem; font-weight: 800; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);">
                        {{ $page }}
                    </span>
                @elseif($page == 1 || $page == $bookings->lastPage() || abs($page - $bookings->currentPage()) <= 2)
                    <a href="{{ $urlWithQuery }}" style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #ffffff; color: #475569; border: 1px solid #cbd5e1; font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: all 0.2s ease;">
                        {{ $page }}
                    </a>
                @elseif(abs($page - $bookings->currentPage()) == 3)
                    <span style="padding: 0.45rem 0.5rem; color: #94a3b8; font-size: 0.8rem; font-weight: 700;">...</span>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($bookings->hasMorePages())
                <a href="{{ $bookings->appends(request()->query())->nextPageUrl() }}" style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                    Next <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                </a>
            @else
                <span style="padding: 0.45rem 0.85rem; border-radius: 8px; background: #e2e8f0; color: #94a3b8; font-size: 0.8rem; font-weight: 700; cursor: not-allowed; display: inline-flex; align-items: center; gap: 0.35rem;">
                    Next <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Modal Partials -->
@include('admin.bookings.modals.create')
@include('admin.bookings.modals.edit')
@include('admin.bookings.modals.preview')

@endsection

@section('scripts')
@include('admin.bookings.scripts')

@if(request('code'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetRow = document.getElementById('booking_row_{{ request('code') }}');
        if (targetRow) {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            @php
                $targetBooking = $bookings->firstWhere('booking_code', request('code'));
            @endphp
            @if($targetBooking)
                setTimeout(function() {
                    openPreviewModal(@json($targetBooking), @json($targetBooking->room));
                }, 300);
            @endif
        }
    });
</script>
@endif
@endsection
