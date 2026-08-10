<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomMaintenance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomMaintenanceController extends Controller
{
    /**
     * Store a newly created room maintenance schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'room_id.required' => 'Pilih kamar terlebih dahulu.',
            'start_date.required' => 'Tanggal mulai maintenance wajib diisi.',
            'end_date.required' => 'Tanggal selesai maintenance wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
        ]);

        $roomId = $request->input('room_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $room = Room::findOrFail($roomId);

        // Rule: Apabila sudah ada booking aktif di rentang tanggal tersebut, tidak bisa maintenance
        $hasActiveBooking = Booking::where('room_id', $roomId)
            ->whereIn('status', [1, 2, 3, 4])
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->where(function ($q) use ($startDate, $endDate) {
                // Check-in date is less than maintenance end date, and Check-out date is greater than maintenance start date
                $q->where('check_in_date', '<=', $endDate)
                  ->where('check_out_date', '>', $startDate);
            })
            ->exists();

        if ($hasActiveBooking) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan maintenance. Kamar ' . $room->name . ' sudah memiliki booking aktif pada rentang tanggal tersebut.');
        }

        // Cek apakah sudah ada jadwal maintenance lain yang bentrok di kamar tersebut
        $hasOverlappingMaintenance = RoomMaintenance::where('room_id', $roomId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            })
            ->exists();

        if ($hasOverlappingMaintenance) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Rentang tanggal maintenance yang dipilih sudah terdaftar sebelumnya untuk kamar ini.');
        }

        RoomMaintenance::create([
            'room_id' => $roomId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'note' => $request->input('note'),
        ]);

        return redirect()->back()->with('success', 'Jadwal maintenance kamar ' . $room->name . ' berhasil disimpan!');
    }

    /**
     * Remove the specified room maintenance schedule from storage.
     */
    public function destroy(RoomMaintenance $maintenance)
    {
        $roomName = $maintenance->room->name ?? 'Kamar';
        $maintenance->delete();

        return redirect()->back()->with('success', 'Jadwal maintenance kamar ' . $roomName . ' berhasil dihapus!');
    }
}
