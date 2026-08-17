<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        // Search filter by name or notes
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengeluaran', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Month-Year filter (Format: YYYY-MM)
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        if (!empty($selectedMonth)) {
            $yearMonth = explode('-', $selectedMonth);
            if (count($yearMonth) === 2) {
                $query->whereYear('tanggal', $yearMonth[0])
                      ->whereMonth('tanggal', $yearMonth[1]);
            }
        }

        // Total calculated for filtered result
        $totalHarga = (clone $query)->sum('harga');
        $totalCount = (clone $query)->count();

        // Get expenses sorted by date descending, then id descending
        $expenses = $query->orderBy('tanggal', 'desc')
                          ->orderBy('id', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        return view('admin.expenses.index', compact(
            'expenses',
            'totalHarga',
            'totalCount',
            'selectedMonth'
        ));
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pengeluaran' => ['required', 'string', 'max:255'],
            'harga'            => ['required', 'numeric', 'min:0'],
            'tanggal'          => ['required', 'date'],
            'keterangan'       => ['nullable', 'string'],
        ], [
            'nama_pengeluaran.required' => 'Nama pengeluaran wajib diisi.',
            'harga.required'            => 'Harga / nominal pengeluaran wajib diisi.',
            'harga.numeric'             => 'Harga harus berupa angka.',
            'harga.min'                 => 'Harga tidak boleh kurang dari 0.',
            'tanggal.required'          => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date'              => 'Format tanggal tidak valid.',
        ]);

        Expense::create([
            'nama_pengeluaran' => $request->input('nama_pengeluaran'),
            'harga'            => $request->input('harga'),
            'tanggal'          => $request->input('tanggal'),
            'keterangan'       => $request->input('keterangan'),
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Catatan pengeluaran berhasil ditambahkan!');
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'nama_pengeluaran' => ['required', 'string', 'max:255'],
            'harga'            => ['required', 'numeric', 'min:0'],
            'tanggal'          => ['required', 'date'],
            'keterangan'       => ['nullable', 'string'],
        ], [
            'nama_pengeluaran.required' => 'Nama pengeluaran wajib diisi.',
            'harga.required'            => 'Harga / nominal pengeluaran wajib diisi.',
            'harga.numeric'             => 'Harga harus berupa angka.',
            'harga.min'                 => 'Harga tidak boleh kurang dari 0.',
            'tanggal.required'          => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date'              => 'Format tanggal tidak valid.',
        ]);

        $expense->update([
            'nama_pengeluaran' => $request->input('nama_pengeluaran'),
            'harga'            => $request->input('harga'),
            'tanggal'          => $request->input('tanggal'),
            'keterangan'       => $request->input('keterangan'),
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'Catatan pengeluaran berhasil diperbarui!');
    }

    /**
     * Remove the specified expense from storage (Soft Delete).
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Catatan pengeluaran berhasil dihapus!');
    }

    /**
     * Display printable PDF report of expenses.
     */
    public function reportPdf(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengeluaran', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $monthName = 'Semua Periode';
        $year = '';

        if (!empty($selectedMonth)) {
            $yearMonth = explode('-', $selectedMonth);
            if (count($yearMonth) === 2) {
                $year = $yearMonth[0];
                $month = $yearMonth[1];
                $query->whereYear('tanggal', $year)
                      ->whereMonth('tanggal', $month);
                $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            }
        }

        $expenses = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();
        $totalHarga = $expenses->sum('harga');
        $totalCount = $expenses->count();

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
        ]);

        return view('admin.expenses.report_pdf', compact(
            'setting', 'expenses', 'totalHarga', 'totalCount', 'selectedMonth', 'monthName', 'year'
        ));
    }

    /**
     * Export expenses to Excel (.xls) file.
     */
    public function reportExcel(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengeluaran', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $monthName = 'Semua Periode';
        $year = '';

        if (!empty($selectedMonth)) {
            $yearMonth = explode('-', $selectedMonth);
            if (count($yearMonth) === 2) {
                $year = $yearMonth[0];
                $month = $yearMonth[1];
                $query->whereYear('tanggal', $year)
                      ->whereMonth('tanggal', $month);
                $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            }
        }

        $expenses = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();
        $totalHarga = $expenses->sum('harga');
        $totalCount = $expenses->count();

        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'homestay_name' => 'Gedambaan Glamping',
            'address' => 'Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
        ]);

        $fileName = 'Laporan_Pengeluaran_' . str_replace(' ', '_', $monthName) . ($year ? '_' . $year : '') . '.xls';

        return response()->view('admin.expenses.report_excel', compact(
            'setting', 'expenses', 'totalHarga', 'totalCount', 'monthName', 'year', 'selectedMonth'
        ))->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
          ->header('Content-Disposition', "attachment; filename=\"$fileName\"")
          ->header('Pragma', 'no-cache')
          ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
          ->header('Expires', '0');
    }
}
