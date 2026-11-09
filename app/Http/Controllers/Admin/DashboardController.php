<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard analytics.
     */
    public function index(Request $request)
    {
        // 1. Calculate Real Stat Metrics
        $totalRooms = Room::count();
        $activeBookings = Booking::whereIn('status', [2, 3, 4])->count();
        $pendingCount = Booking::where('status', 1)->count();

        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hasDateFilter = $request->filled('start_date') || $request->filled('end_date');

        $applyBookingDateFilter = function ($q) use ($hasDateFilter, $startDate, $endDate, $currentYear, $currentMonth) {
            if ($hasDateFilter) {
                if (!empty($startDate)) {
                    $q->whereDate('check_in_date', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('check_in_date', '<=', $endDate);
                }
            } else {
                $q->whereYear('check_in_date', $currentYear)
                  ->whereMonth('check_in_date', (int)$currentMonth);
            }
        };

        $applyExpenseDateFilter = function ($q) use ($hasDateFilter, $startDate, $endDate, $currentYear, $currentMonth) {
            if ($hasDateFilter) {
                if (!empty($startDate)) {
                    $q->whereDate('tanggal', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('tanggal', '<=', $endDate);
                }
            } else {
                $q->whereYear('tanggal', $currentYear)
                  ->whereMonth('tanggal', (int)$currentMonth);
            }
        };

        $monthlyActiveBookings = Booking::whereIn('status', [2, 3, 4])
            ->tap($applyBookingDateFilter)
            ->count();

        $monthlyLunasRevenue = Booking::whereIn('status', [2, 3])
            ->tap($applyBookingDateFilter)
            ->sum('total_price');

        $monthlyDpTotal = Booking::where('status', 4)
            ->tap($applyBookingDateFilter)
            ->sum('total_price');

        // Pendapatan Riil: Lunas/Selesai (100%) + DP 50% (dihitung 50% yang sudah masuk rekening)
        $monthlyRealRevenue = $monthlyLunasRevenue + ($monthlyDpTotal * 0.5);

        // Estimasi Pendapatan: Lunas/Selesai (100%) + DP 50% (dihitung 100% jika lunas nanti)
        $monthlyRevenue = $monthlyLunasRevenue + $monthlyDpTotal;

        $monthlyExpense = Expense::query()
            ->tap($applyExpenseDateFilter)
            ->sum('harga');

        $netIncome = $monthlyRevenue - $monthlyExpense;

        // 2. Prepare Grouped Bar Chart Data for selected month or date range
        $chartLabels = [];
        $chartDataLunas = [];
        $chartDataBatal = [];

        if ($hasDateFilter) {
            $chartStart = !empty($startDate) ? Carbon::parse($startDate) : Carbon::parse($endDate)->startOfMonth();
            $chartEnd = !empty($endDate) ? Carbon::parse($endDate) : Carbon::parse($startDate)->endOfMonth();
            if ($chartEnd->lt($chartStart)) {
                [$chartStart, $chartEnd] = [$chartEnd, $chartStart];
            }
            // Limit chart loop to max 62 days for readability
            if ($chartStart->diffInDays($chartEnd) > 62) {
                $chartEnd = (clone $chartStart)->addDays(62);
            }

            $cursor = clone $chartStart;
            while ($cursor->lte($chartEnd)) {
                $dateStr = $cursor->format('Y-m-d');
                $chartLabels[] = $cursor->format('d M');

                $lunasCount = Booking::whereIn('status', [2, 3, 4])->whereDate('check_in_date', $dateStr)->count();
                $batalCount = Booking::where('status', 0)->whereDate('check_in_date', $dateStr)->count();

                $chartDataLunas[] = $lunasCount;
                $chartDataBatal[] = $batalCount;
                $cursor->addDay();
            }
        } else {
            $daysInMonth = Carbon::createFromDate((int)$currentYear, (int)$currentMonth, 1)->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                $chartLabels[] = $day . ' ' . Carbon::createFromDate((int)$currentYear, (int)$currentMonth, 1)->format('M');

                $lunasCount = Booking::whereIn('status', [2, 3, 4])->whereDate('check_in_date', $dateStr)->count();
                $batalCount = Booking::where('status', 0)->whereDate('check_in_date', $dateStr)->count();

                $chartDataLunas[] = $lunasCount;
                $chartDataBatal[] = $batalCount;
            }
        }

        // Return JSON for AJAX filter request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'labels' => $chartLabels,
                'lunas' => $chartDataLunas,
                'batal' => $chartDataBatal,
                'monthly_active_bookings' => $monthlyActiveBookings . ' Pemesanan',
                'formatted_real_revenue' => 'Rp ' . number_format($monthlyRealRevenue, 0, ',', '.'),
                'formatted_revenue' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'),
                'formatted_expense' => 'Rp ' . number_format($monthlyExpense, 0, ',', '.'),
                'formatted_net' => ($netIncome >= 0 ? 'Rp ' : '-Rp ') . number_format(abs($netIncome), 0, ',', '.'),
            ]);
        }

        $stats = [
            'total_rooms' => $totalRooms,
            'active_bookings' => $activeBookings,
            'monthly_active_bookings' => $monthlyActiveBookings,
            'monthly_real_revenue' => $monthlyRealRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_expense' => $monthlyExpense,
            'net_income' => $netIncome,
            'pending_count' => $pendingCount,
        ];

        return view('admin.dashboard.index', compact(
            'stats',
            'currentMonth',
            'currentYear',
            'startDate',
            'endDate',
            'chartLabels',
            'chartDataLunas',
            'chartDataBatal'
        ));
    }

    /**
     * Display printable PDF report of monthly / date-filtered bookings.
     */
    public function reportPdf(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hasDateFilter = $request->filled('start_date') || $request->filled('end_date');

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
            'wa_number' => '08776905151',
        ]);

        $bookingQuery = Booking::with('room');
        $expenseQuery = Expense::query();

        if ($hasDateFilter) {
            if (!empty($startDate)) {
                $bookingQuery->whereDate('check_in_date', '>=', $startDate);
                $expenseQuery->whereDate('tanggal', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $bookingQuery->whereDate('check_in_date', '<=', $endDate);
                $expenseQuery->whereDate('tanggal', '<=', $endDate);
            }
            if (!empty($startDate) && !empty($endDate)) {
                $monthName = Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . Carbon::parse($endDate)->format('d/m/Y');
            } elseif (!empty($startDate)) {
                $monthName = 'Mulai ' . Carbon::parse($startDate)->format('d/m/Y');
            } else {
                $monthName = 'Sampai ' . Carbon::parse($endDate)->format('d/m/Y');
            }
            $year = '';
        } else {
            $bookingQuery->whereYear('check_in_date', $year)->whereMonth('check_in_date', $month);
            $expenseQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        }

        $bookings = $bookingQuery->orderBy('check_in_date', 'asc')->get();
        $expenses = $expenseQuery->sum('harga');

        $lunasRevenue = $bookings->whereIn('status', [2, 3])->sum('total_price');
        $dpTotal = $bookings->where('status', 4)->sum('total_price');
        $realRevenue = $lunasRevenue + ($dpTotal * 0.5);
        $revenue = $lunasRevenue + $dpTotal;

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
            'real_revenue' => $realRevenue,
            'revenue' => $revenue,
            'expense' => $expenses,
            'net_income' => $revenue - $expenses,
        ];

        return view('admin.dashboard.report_pdf', compact('setting', 'bookings', 'summary', 'month', 'year', 'monthName'));
    }

    /**
     * Export monthly / date-filtered bookings to beautifully formatted Excel (.xls) file.
     */
    public function reportExcel(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hasDateFilter = $request->filled('start_date') || $request->filled('end_date');

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
        ]);

        $bookingQuery = Booking::with('room');
        $expenseQuery = Expense::query();

        if ($hasDateFilter) {
            if (!empty($startDate)) {
                $bookingQuery->whereDate('check_in_date', '>=', $startDate);
                $expenseQuery->whereDate('tanggal', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $bookingQuery->whereDate('check_in_date', '<=', $endDate);
                $expenseQuery->whereDate('tanggal', '<=', $endDate);
            }
            if (!empty($startDate) && !empty($endDate)) {
                $monthName = Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . Carbon::parse($endDate)->format('d/m/Y');
                $fileSuffix = $startDate . '_sd_' . $endDate;
            } elseif (!empty($startDate)) {
                $monthName = 'Mulai ' . Carbon::parse($startDate)->format('d/m/Y');
                $fileSuffix = 'Mulai_' . $startDate;
            } else {
                $monthName = 'Sampai ' . Carbon::parse($endDate)->format('d/m/Y');
                $fileSuffix = 'Sampai_' . $endDate;
            }
            $year = '';
            $fileName = 'Laporan_Pemesanan_' . $fileSuffix . '.xls';
        } else {
            $bookingQuery->whereYear('check_in_date', $year)->whereMonth('check_in_date', $month);
            $expenseQuery->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            $fileName = 'Laporan_Pemesanan_' . str_replace(' ', '_', $monthName) . '_' . $year . '.xls';
        }

        $bookings = $bookingQuery->orderBy('check_in_date', 'asc')->get();
        $expenses = $expenseQuery->sum('harga');

        $lunasRevenue = $bookings->whereIn('status', [2, 3])->sum('total_price');
        $dpTotal = $bookings->where('status', 4)->sum('total_price');
        $realRevenue = $lunasRevenue + ($dpTotal * 0.5);
        $revenue = $lunasRevenue + $dpTotal;

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
            'real_revenue' => $realRevenue,
            'revenue' => $revenue,
            'expense' => $expenses,
            'net_income' => $revenue - $expenses,
        ];

        return response()->view('admin.dashboard.report_excel', compact(
            'setting', 'bookings', 'summary', 'monthName', 'year'
        ))->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
          ->header('Content-Disposition', "attachment; filename=\"$fileName\"")
          ->header('Pragma', 'no-cache')
          ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
          ->header('Expires', '0');
    }
}
