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

        $monthlyActiveBookings = Booking::whereIn('status', [2, 3, 4])
            ->whereYear('check_in_date', $currentYear)
            ->whereMonth('check_in_date', (int)$currentMonth)
            ->count();

        $monthlyLunasRevenue = Booking::whereIn('status', [2, 3])
            ->whereYear('check_in_date', $currentYear)
            ->whereMonth('check_in_date', (int)$currentMonth)
            ->sum('total_price');

        $monthlyDpTotal = Booking::where('status', 4)
            ->whereYear('check_in_date', $currentYear)
            ->whereMonth('check_in_date', (int)$currentMonth)
            ->sum('total_price');

        // Pendapatan Riil: Lunas/Selesai (100%) + DP 50% (dihitung 50% yang sudah masuk rekening)
        $monthlyRealRevenue = $monthlyLunasRevenue + ($monthlyDpTotal * 0.5);

        // Estimasi Pendapatan: Lunas/Selesai (100%) + DP 50% (dihitung 100% jika lunas nanti)
        $monthlyRevenue = $monthlyLunasRevenue + $monthlyDpTotal;

        $monthlyExpense = Expense::whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', (int)$currentMonth)
            ->sum('harga');

        $netIncome = $monthlyRevenue - $monthlyExpense;

        // 2. Prepare Grouped Bar Chart Data for selected month
        $daysInMonth = Carbon::createFromDate((int)$currentYear, (int)$currentMonth, 1)->daysInMonth;
        $chartLabels = [];
        $chartDataLunas = [];
        $chartDataBatal = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $chartLabels[] = $day . ' ' . Carbon::createFromDate((int)$currentYear, (int)$currentMonth, 1)->format('M');

            $lunasCount = Booking::whereIn('status', [2, 3, 4])->whereDate('check_in_date', $dateStr)->count();
            $batalCount = Booking::where('status', 0)->whereDate('check_in_date', $dateStr)->count();

            $chartDataLunas[] = $lunasCount;
            $chartDataBatal[] = $batalCount;
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
            'chartLabels',
            'chartDataLunas',
            'chartDataBatal'
        ));
    }

    /**
     * Display printable PDF report of monthly bookings.
     */
    public function reportPdf(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
            'wa_number' => '08776905151',
        ]);

        $bookings = Booking::with('room')
            ->whereYear('check_in_date', $year)
            ->whereMonth('check_in_date', $month)
            ->orderBy('check_in_date', 'asc')
            ->get();

        $expenses = Expense::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('harga');

        $revenue = $bookings->whereIn('status', [2, 3, 4])->sum('total_price');

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
            'revenue' => $revenue,
            'expense' => $expenses,
            'net_income' => $revenue - $expenses,
        ];

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        return view('admin.dashboard.report_pdf', compact('setting', 'bookings', 'summary', 'month', 'year', 'monthName'));
    }

    /**
     * Export monthly bookings to beautifully formatted Excel (.xls) file.
     */
    public function reportExcel(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
        ]);

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        $fileName = 'Laporan_Pemesanan_' . str_replace(' ', '_', $monthName) . '_' . $year . '.xls';

        $bookings = Booking::with('room')
            ->whereYear('check_in_date', $year)
            ->whereMonth('check_in_date', $month)
            ->orderBy('check_in_date', 'asc')
            ->get();

        $expenses = Expense::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->sum('harga');

        $revenue = $bookings->whereIn('status', [2, 3, 4])->sum('total_price');

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
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
