<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
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
        $activeBookings = Booking::whereIn('status', [1, 2, 3, 4])->count();
        $pendingCount = Booking::where('status', 1)->count();

        $currentMonth = $request->input('month', date('m'));
        $currentYear = $request->input('year', date('Y'));

        $monthlyRevenue = Booking::whereIn('status', [2, 3, 4])
            ->whereYear('check_in_date', $currentYear)
            ->whereMonth('check_in_date', (int)$currentMonth)
            ->sum('total_price');

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
                'formatted_revenue' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'),
            ]);
        }

        $stats = [
            'total_rooms' => $totalRooms,
            'active_bookings' => $activeBookings,
            'monthly_revenue' => $monthlyRevenue,
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

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
            'revenue' => $bookings->whereIn('status', [2, 3, 4])->sum('total_price'),
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

        $summary = [
            'total' => $bookings->count(),
            'lunas' => $bookings->whereIn('status', [2, 3])->count(),
            'dp' => $bookings->where('status', 4)->count(),
            'pending' => $bookings->where('status', 1)->count(),
            'batal' => $bookings->where('status', 0)->count(),
            'revenue' => $bookings->whereIn('status', [2, 3, 4])->sum('total_price'),
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
