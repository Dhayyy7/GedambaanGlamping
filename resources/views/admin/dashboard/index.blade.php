@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')
@section('page_title', 'Ringkasan Dashboard Analytics')

@section('styles')
<style>
    /* Stats Grid - Neat 4x2 Layout */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 1280px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 1.25rem 1.35rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 104px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
    }

    .stat-icon {
        width: 52px;
        height: 52px;
        min-width: 52px;
        flex-shrink: 0;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        position: relative;
    }

    .icon-indigo { background-color: #e0e7ff; color: #4f46e5; }
    .icon-emerald { background-color: #dcfce7; color: #16a34a; }
    .icon-teal { background-color: #ccfbf1; color: #0d9488; }
    .icon-amber { background-color: #fef3c7; color: #d97706; }
    .icon-rose { background-color: #ffe4e6; color: #e11d48; }
    .icon-red { background-color: #fee2e2; color: #dc2626; }
    .icon-cyan { background-color: #e0f2fe; color: #0284c7; }
    .icon-purple { background-color: #f3e8ff; color: #7e22ce; }

    /* Red Dot Pulsing Notification Badge */
    .notification-badge-dot {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 14px;
        height: 14px;
        background-color: #ef4444;
        border: 2px solid #ffffff;
        border-radius: 50%;
        animation: pulse-red 1.5s infinite;
    }

    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .stat-details {
        min-width: 0;
        flex: 1;
    }

    .stat-details h3 {
        font-size: 0.74rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.3rem;
        line-height: 1.3;
    }

    .stat-details .value {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        white-space: nowrap;
    }

    .stat-subtext {
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 0.2rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* Section Cards */
    .card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-select-filter {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-select-filter:focus {
        border-color: #4f46e5;
        background-color: #ffffff;
    }

    .btn-report {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.9rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-decoration: none;
    }

    .btn-report:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endsection

@section('content')

    <!-- KPI Stat Cards (4x2 Grid) -->
    <div class="stats-grid">
        <!-- Row 1, Col 1: Total Kamar -->
        <div class="stat-card">
            <div class="stat-icon icon-indigo">
                <i class="fa-solid fa-bed"></i>
            </div>
            <div class="stat-details">
                <h3>Total Kamar & Unit</h3>
                <div class="value">{{ $stats['total_rooms'] }} Unit</div>
            </div>
        </div>

        <!-- Row 1, Col 2: Pemesanan Aktif -->
        <div class="stat-card">
            <div class="stat-icon icon-emerald">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                <h3>Pemesanan Aktif</h3>
                <div class="value">{{ $stats['active_bookings'] }} Pemesanan</div>
            </div>
        </div>

        <!-- Row 1, Col 3: Pemesanan Aktif Bulan Ini -->
        <div class="stat-card">
            <div class="stat-icon icon-teal">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="stat-details">
                <h3>Pemesanan Aktif Bulan Ini</h3>
                <div class="value" id="monthly_active_bookings_display">{{ $stats['monthly_active_bookings'] }} Pemesanan</div>
            </div>
        </div>

        <!-- Row 1, Col 4: Booking Pending Notification -->
        <a href="{{ route('admin.bookings.index') }}" class="stat-card">
            <div class="stat-icon icon-rose">
                <i class="fa-solid fa-bell"></i>
                @if($stats['pending_count'] > 0)
                    <span class="notification-badge-dot"></span>
                @endif
            </div>
            <div class="stat-details">
                <h3>Booking Pending</h3>
                @if($stats['pending_count'] > 0)
                    <div class="value" style="color: #dc2626;">{{ $stats['pending_count'] }} Pemesanan</div>
                    <div class="stat-subtext" style="color: #dc2626; font-weight: 700;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Kunci 2 Jam - Perlu Cek
                    </div>
                @else
                    <div class="value" style="color: #16a34a;">0 Pending</div>
                    <div class="stat-subtext" style="color: #16a34a;">
                        <i class="fa-solid fa-circle-check"></i> Tidak Ada Antrean
                    </div>
                @endif
            </div>
        </a>

        <!-- Row 2, Col 1: Pendapatan Riil Bulan Ini (Di Samping Kiri Estimasi Pendapatan) -->
        <div class="stat-card">
            <div class="stat-icon icon-emerald">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="stat-details">
                <h3>Pendapatan Riil Bulan Ini</h3>
                <div class="value" id="monthly_real_revenue_display" style="color: #16a34a;">Rp {{ number_format($stats['monthly_real_revenue'], 0, ',', '.') }}</div>
                <div class="stat-subtext">
                    <i class="fa-solid fa-building-columns" style="color: #16a34a;"></i> Uang Masuk (Lunas + DP 50%)
                </div>
            </div>
        </div>

        <!-- Row 2, Col 2: Estimasi Pendapatan Bulan Ini -->
        <div class="stat-card">
            <div class="stat-icon icon-amber">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <h3>Estimasi Pendapatan Bulan Ini</h3>
                <div class="value" id="monthly_revenue_display">Rp {{ number_format($stats['monthly_revenue'], 0, ',', '.') }}</div>
                <div class="stat-subtext">
                    <i class="fa-solid fa-coins" style="color: #d97706;"></i> Total Penuh (Jika DP Lunas)
                </div>
            </div>
        </div>

        <!-- Row 2, Col 3: Pengeluaran Bulan Ini -->
        <a href="{{ route('admin.expenses.index') }}" class="stat-card">
            <div class="stat-icon icon-red">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-details">
                <h3>Pengeluaran Bulan Ini</h3>
                <div class="value" id="monthly_expense_display">Rp {{ number_format($stats['monthly_expense'], 0, ',', '.') }}</div>
                <div class="stat-subtext">
                    <i class="fa-solid fa-arrow-trend-down" style="color: #dc2626;"></i> Total Biaya Operasional
                </div>
            </div>
        </a>

        <!-- Row 2, Col 4: Total (Pendapatan - Pengeluaran) -->
        <div class="stat-card">
            <div class="stat-icon icon-cyan">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div class="stat-details">
                <h3>Total (Pendapatan - Pengeluaran)</h3>
                <div class="value" id="net_income_display">
                    @if($stats['net_income'] < 0)
                        -Rp {{ number_format(abs($stats['net_income']), 0, ',', '.') }}
                    @else
                        Rp {{ number_format($stats['net_income'], 0, ',', '.') }}
                    @endif
                </div>
                <div class="stat-subtext">
                    <i class="fa-solid fa-calculator" style="color: #0284c7;"></i> Estimasi Bersih Bulan Ini
                </div>
            </div>
        </div>
    </div>

    <!-- Section Grafik Pemesanan Homestay (Grouped Bar Chart: Lunas vs Dibatalkan) -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">
                    <i class="fa-solid fa-chart-column" style="color: #4f46e5;"></i>
                    Grafik Pemesanan Homestay (Lunas vs Dibatalkan)
                </h2>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                    Perbandingan jumlah pesanan <span style="color: #2563eb; font-weight: 700;">Lunas (Biru)</span> dan <span style="color: #dc2626; font-weight: 700;">Dibatalkan (Merah)</span> per hari.
                </div>
            </div>

            <!-- Month & Date Range Filter + Report Download -->
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <label for="month_filter" style="font-size: 0.82rem; font-weight: 700; color: #334155; white-space: nowrap;">Bulan:</label>
                    <select id="month_filter" class="form-select-filter" onchange="onMonthFilterChange()">
                        @for($m = 1; $m <= 12; $m++)
                            @php $mStr = sprintf('%02d', $m); @endphp
                            <option value="{{ $mStr }}" {{ $mStr == $currentMonth ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }} {{ $currentYear }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 0.4rem; background: #f8fafc; padding: 0.25rem 0.6rem; border-radius: 10px; border: 1px solid #cbd5e1; flex-wrap: wrap;">
                    <span style="font-size: 0.78rem; font-weight: 700; color: #475569; white-space: nowrap;">
                        <i class="fa-regular fa-calendar" style="color: #4f46e5;"></i> Tanggal:
                    </span>
                    <input type="date" id="start_date_filter" value="{{ $startDate ?? '' }}" class="form-select-filter" style="padding: 0.3rem 0.5rem; font-size: 0.8rem; border-radius: 8px; background: #ffffff;" onchange="updateChartData()" title="Dari Tanggal">
                    <span style="font-size: 0.78rem; color: #64748b; font-weight: 600;">s/d</span>
                    <input type="date" id="end_date_filter" value="{{ $endDate ?? '' }}" class="form-select-filter" style="padding: 0.3rem 0.5rem; font-size: 0.8rem; border-radius: 8px; background: #ffffff;" onchange="updateChartData()" title="Sampai Tanggal">
                    <button type="button" id="btn_reset_date" onclick="resetDateFilter()" style="display: {{ (!empty($startDate) || !empty($endDate)) ? 'inline-flex' : 'none' }}; align-items: center; gap: 0.25rem; background: #fee2e2; color: #dc2626; border: none; padding: 0.3rem 0.55rem; border-radius: 7px; font-size: 0.75rem; font-weight: 700; cursor: pointer;" title="Reset Filter Tanggal">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </button>
                </div>

                <!-- Report Export Action Buttons -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="button" onclick="exportReport('pdf')" class="btn-report" style="background: #4f46e5; color: #ffffff;" title="Cetak Laporan Pemesanan PDF">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Cetak PDF</span>
                    </button>
                    <button type="button" onclick="exportReport('excel')" class="btn-report" style="background: #16a34a; color: #ffffff;" title="Unduh File CSV / Excel">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Excel / CSV</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chart Canvas Container -->
        <div style="position: relative; height: 380px; width: 100%;">
            <canvas id="bookingChart"></canvas>
        </div>
    </div>

@endsection

@section('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let bookingChart = null;

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('bookingChart').getContext('2d');

        const initialLabels = @json($chartLabels);
        const initialLunas = @json($chartDataLunas);
        const initialBatal = @json($chartDataBatal);

        bookingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: initialLabels,
                datasets: [
                    {
                        label: 'Pesanan Lunas',
                        data: initialLunas,
                        backgroundColor: '#3b82f6', // Biru
                        borderColor: '#2563eb',
                        borderWidth: 1,
                        borderRadius: 6,
                    },
                    {
                        label: 'Pesanan Dibatalkan',
                        data: initialBatal,
                        backgroundColor: '#ef4444', // Merah
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Plus Jakarta Sans',
                                size: 12,
                                weight: '700'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y + ' Pemesanan';
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Jumlah Pemesanan (Unit)' },
                        ticks: { stepSize: 1, precision: 0 },
                        min: 0,
                    }
                }
            }
        });
    });

    function onMonthFilterChange() {
        document.getElementById('start_date_filter').value = '';
        document.getElementById('end_date_filter').value = '';
        updateChartData();
    }

    function resetDateFilter() {
        document.getElementById('start_date_filter').value = '';
        document.getElementById('end_date_filter').value = '';
        updateChartData();
    }

    function updateChartData() {
        const selectedMonth = document.getElementById('month_filter').value;
        const startDate = document.getElementById('start_date_filter').value;
        const endDate = document.getElementById('end_date_filter').value;
        const currentYear = '{{ $currentYear }}';

        const resetBtn = document.getElementById('btn_reset_date');
        if (resetBtn) {
            resetBtn.style.display = (startDate || endDate) ? 'inline-flex' : 'none';
        }

        const params = new URLSearchParams({
            month: selectedMonth,
            year: currentYear
        });
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);

        fetch(`/admin/dashboard?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (bookingChart) {
                bookingChart.data.labels = data.labels;
                bookingChart.data.datasets[0].data = data.lunas;
                bookingChart.data.datasets[1].data = data.batal;
                bookingChart.update();
            }

            if (data.monthly_active_bookings) {
                document.getElementById('monthly_active_bookings_display').innerText = data.monthly_active_bookings;
            }
            if (data.formatted_real_revenue) {
                document.getElementById('monthly_real_revenue_display').innerText = data.formatted_real_revenue;
            }
            if (data.formatted_revenue) {
                document.getElementById('monthly_revenue_display').innerText = data.formatted_revenue;
            }
            if (data.formatted_expense) {
                document.getElementById('monthly_expense_display').innerText = data.formatted_expense;
            }
            if (data.formatted_net) {
                document.getElementById('net_income_display').innerText = data.formatted_net;
            }
        })
        .catch(error => {
            console.error('Error fetching chart data:', error);
        });
    }

    function exportReport(type) {
        const selectedMonth = document.getElementById('month_filter').value;
        const startDate = document.getElementById('start_date_filter').value;
        const endDate = document.getElementById('end_date_filter').value;
        const currentYear = '{{ $currentYear }}';

        const params = new URLSearchParams({
            month: selectedMonth,
            year: currentYear
        });
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);

        if (type === 'pdf') {
            const url = `{{ route('admin.dashboard.report.pdf') }}?${params.toString()}`;
            window.open(url, '_blank');
        } else if (type === 'excel') {
            const url = `{{ route('admin.dashboard.report.excel') }}?${params.toString()}`;
            window.location.href = url;
        }
    }
</script>
@endsection
