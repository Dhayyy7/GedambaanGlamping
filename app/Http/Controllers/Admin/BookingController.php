<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomMaintenance;
use App\Models\ExtraFacility;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Helper to auto-update booking statuses (Expiration & Check-Out Completion).
     */
    private function autoUpdateBookingStatuses()
    {
        // 1. Auto cancel pending bookings older than 2 hours (Status 1 -> 0)
        Booking::where('status', 1)
            ->where('expired_at', '<=', now())
            ->update(['status' => 0]);

        // 2. Auto complete paid/DP bookings whose check-out date has passed (Status 2, 4 -> 3)
        Booking::whereIn('status', [2, 4])
            ->where('check_out_date', '<', date('Y-m-d'))
            ->update(['status' => 3]);
    }

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $this->autoUpdateBookingStatuses();

        $search = $request->input('search', $request->input('code'));
        $status = $request->input('status');

        $query = Booking::with('room')->latest();

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhere('customer_address', 'LIKE', "%{$search}%")
                  ->orWhereHas('room', function ($rq) use ($search) {
                      $rq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%");
                  });

                // Match status text keywords
                $searchLower = strtolower($search);
                if (str_contains('lunas', $searchLower)) {
                    $q->orWhere('status', 2);
                } elseif (str_contains('dp', $searchLower) || str_contains('50', $searchLower)) {
                    $q->orWhere('status', 4);
                } elseif (str_contains('pending', $searchLower) || str_contains('menunggu', $searchLower)) {
                    $q->orWhere('status', 1);
                } elseif (str_contains('selesai', $searchLower) || str_contains('completed', $searchLower)) {
                    $q->orWhere('status', 3);
                } elseif (str_contains('batal', $searchLower) || str_contains('expired', $searchLower)) {
                    $q->orWhere('status', 0);
                }
            });
        }

        $bookings = $query->paginate(10)->withQueryString();
        $rooms = Room::all();
        $extraFacilities = ExtraFacility::all();

        return view('admin.bookings.index', compact('bookings', 'rooms', 'extraFacilities', 'search', 'status'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $this->autoUpdateBookingStatuses();

        $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_sosmed' => ['nullable', 'string', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'admin_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', 'integer', 'in:0,1,2,3,4'],
            'extra_facility_ids' => ['nullable', 'array'],
            'extra_facility_ids.*' => ['exists:extra_facilities,id'],
        ], [
            'room_id.required' => 'Pilih kamar terlebih dahulu.',
            'customer_name.required' => 'Nama pemesan wajib diisi.',
            'customer_address.required' => 'Alamat pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor HP pemesan wajib diisi.',
            'check_in_date.required' => 'Tanggal Check-in wajib diisi.',
            'check_out_date.required' => 'Tanggal Check-out wajib diisi.',
            'check_out_date.after' => 'Tanggal Check-out harus setelah Tanggal Check-in.',
            'admin_discount.numeric' => 'Diskon Admin harus berupa angka persentase (0 - 100).',
            'admin_discount.min' => 'Diskon Admin minimal 0%.',
            'admin_discount.max' => 'Diskon Admin maksimal 100%.',
        ]);

        $roomId = $request->input('room_id');
        $checkInStr = $request->input('check_in_date');
        $checkOutStr = $request->input('check_out_date');

        // Check availability for overlaps with active bookings (status 1, 2, 3, or 4)
        $isConflict = Booking::where('room_id', $roomId)
            ->whereIn('status', [1, 2, 3, 4])
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('check_in_date', '<', $checkOutStr)
                  ->where('check_out_date', '>', $checkInStr);
            })
            ->exists();

        if ($isConflict) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kamar sudah terbooking pada tanggal tersebut.');
        }

        // Check maintenance conflict
        $isMaintenanceConflict = RoomMaintenance::where('room_id', $roomId)
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('start_date', '<', $checkOutStr)
                  ->where('end_date', '>=', $checkInStr);
            })
            ->exists();

        if ($isMaintenanceConflict) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kamar sedang dalam masa pemeliharaan (maintenance) pada tanggal tersebut.');
        }

        $room = Room::findOrFail($roomId);
        $checkIn = Carbon::parse($checkInStr);
        $checkOut = Carbon::parse($checkOutStr);

        $adminDiscount = max(0, min(100, (float) $request->input('admin_discount', 0)));

        // Calculate dynamic room total price based on Weekday vs Weekend/Holiday nights
        $bookingDetails = $room->calculateBookingDetails($checkInStr, $checkOutStr);
        $totalNights = $bookingDetails['total_nights'];
        
        $roomOriginalPrice = $bookingDetails['total_original_price'];
        if ($adminDiscount > 0) {
            $roomTotalPrice = $roomOriginalPrice - ($roomOriginalPrice * ($adminDiscount / 100));
        } else {
            $roomTotalPrice = $bookingDetails['total_final_price'];
        }

        // Code format: Kode Kamar + Tanggal Check-in YYYYMMDD (e.g. P1V120260729)
        $baseCode = strtoupper($room->code) . $checkIn->format('Ymd');
        $bookingCode = $baseCode;
        $counter = 1;
        while (Booking::withTrashed()->where('booking_code', $bookingCode)->exists()) {
            $bookingCode = $baseCode . '-' . $counter;
            $counter++;
        }

        $roomPrice = $room->price;
        $discount = $room->discount; // percent % from room if available, else null

        // Process selected Extra Facilities
        $selectedExtraIds = $request->input('extra_facility_ids', []);
        $extraFacilitiesList = ExtraFacility::whereIn('id', $selectedExtraIds)->get();

        $totalExtraPrice = 0;
        $savedExtraFacilities = [];
        foreach ($extraFacilitiesList as $ef) {
            $totalExtraPrice += $ef->price;
            $savedExtraFacilities[] = [
                'id' => $ef->id,
                'name' => $ef->name,
                'price' => (float) $ef->price,
            ];
        }

        $totalPrice = $roomTotalPrice + $totalExtraPrice;

        $status = $request->input('status', 1); // default 1 = Pending
        $expiredAt = ($status == 1) ? Carbon::now()->addHours(2) : null;

        Booking::create([
            'room_id' => $room->id,
            'booking_code' => $bookingCode,
            'customer_name' => $request->input('customer_name'),
            'customer_address' => $request->input('customer_address'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_sosmed' => $request->input('customer_sosmed'),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'total_nights' => $totalNights,
            'room_price' => $roomPrice,
            'discount' => $discount,
            'admin_discount' => $adminDiscount,
            'total_price' => $totalPrice,
            'status' => $status,
            'expired_at' => $expiredAt,
            'extra_facilities' => $savedExtraFacilities,
        ]);

        return redirect()->route('admin.bookings.index')->with('success', 'Pemesanan baru berhasil disimpan dengan Kode: ' . $bookingCode);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        $this->autoUpdateBookingStatuses();

        $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_sosmed' => ['nullable', 'string', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'admin_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'integer', 'in:0,1,2,3,4'],
            'extra_facility_ids' => ['nullable', 'array'],
            'extra_facility_ids.*' => ['exists:extra_facilities,id'],
        ], [
            'room_id.required' => 'Pilih kamar terlebih dahulu.',
            'customer_name.required' => 'Nama pemesan wajib diisi.',
            'customer_address.required' => 'Alamat pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor HP pemesan wajib diisi.',
            'check_in_date.required' => 'Tanggal Check-in wajib diisi.',
            'check_out_date.required' => 'Tanggal Check-out wajib diisi.',
            'check_out_date.after' => 'Tanggal Check-out harus setelah Tanggal Check-in.',
            'admin_discount.numeric' => 'Diskon Admin harus berupa angka persentase (0 - 100).',
            'admin_discount.min' => 'Diskon Admin minimal 0%.',
            'admin_discount.max' => 'Diskon Admin maksimal 100%.',
        ]);

        $roomId = $request->input('room_id');
        $checkInStr = $request->input('check_in_date');
        $checkOutStr = $request->input('check_out_date');

        $newStatus = (int) $request->input('status');

        // If status is active (1, 2, 3, or 4), check for conflicts with OTHER active bookings
        if (in_array($newStatus, [1, 2, 3, 4])) {
            $isConflict = Booking::where('room_id', $roomId)
                ->where('id', '!=', $booking->id)
                ->whereIn('status', [1, 2, 3, 4])
                ->where(function ($q) {
                    $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
                })
                ->where(function ($q) use ($checkInStr, $checkOutStr) {
                    $q->where('check_in_date', '<', $checkOutStr)
                      ->where('check_out_date', '>', $checkInStr);
                })
                ->exists();

            if ($isConflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Kamar sudah terbooking pada tanggal tersebut.');
            }

            // Check maintenance conflict
            $isMaintenanceConflict = RoomMaintenance::where('room_id', $roomId)
                ->where(function ($q) use ($checkInStr, $checkOutStr) {
                    $q->where('start_date', '<', $checkOutStr)
                      ->where('end_date', '>=', $checkInStr);
                })
                ->exists();

            if ($isMaintenanceConflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Kamar sedang dalam masa pemeliharaan (maintenance) pada tanggal tersebut.');
            }
        }

        $room = Room::findOrFail($roomId);
        $checkIn = Carbon::parse($checkInStr);
        $checkOut = Carbon::parse($checkOutStr);

        $adminDiscount = max(0, min(100, (float) $request->input('admin_discount', 0)));

        // Calculate dynamic room total price based on Weekday vs Weekend/Holiday nights
        $bookingDetails = $room->calculateBookingDetails($checkInStr, $checkOutStr);
        $totalNights = $bookingDetails['total_nights'];

        $roomOriginalPrice = $bookingDetails['total_original_price'];
        if ($adminDiscount > 0) {
            $roomTotalPrice = $roomOriginalPrice - ($roomOriginalPrice * ($adminDiscount / 100));
        } else {
            $roomTotalPrice = $bookingDetails['total_final_price'];
        }

        // Recalculate code if room or checkin date changed
        $baseCode = strtoupper($room->code) . $checkIn->format('Ymd');
        $bookingCode = $booking->booking_code;
        if (!str_starts_with($bookingCode, $baseCode)) {
            $bookingCode = $baseCode;
            $counter = 1;
            while (Booking::withTrashed()->where('booking_code', $bookingCode)->where('id', '!=', $booking->id)->exists()) {
                $bookingCode = $baseCode . '-' . $counter;
                $counter++;
            }
        }

        $roomPrice = $room->price;
        $discount = $room->discount;

        // Process selected Extra Facilities
        $selectedExtraIds = $request->input('extra_facility_ids', []);
        $extraFacilitiesList = ExtraFacility::whereIn('id', $selectedExtraIds)->get();

        $totalExtraPrice = 0;
        $savedExtraFacilities = [];
        foreach ($extraFacilitiesList as $ef) {
            $totalExtraPrice += $ef->price;
            $savedExtraFacilities[] = [
                'id' => $ef->id,
                'name' => $ef->name,
                'price' => (float) $ef->price,
            ];
        }

        $totalPrice = $roomTotalPrice + $totalExtraPrice;

        // Manage expired_at: if changing to 1 (pending), extend 2 hours. If 2 (lunas), 3 (selesai) or 0 (batal), clear expired_at
        $expiredAt = $booking->expired_at;
        if ($newStatus == 1 && $booking->status != 1) {
            $expiredAt = Carbon::now()->addHours(2);
        } elseif ($newStatus != 1) {
            $expiredAt = null;
        }

        $booking->update([
            'room_id' => $room->id,
            'booking_code' => $bookingCode,
            'customer_name' => $request->input('customer_name'),
            'customer_address' => $request->input('customer_address'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_sosmed' => $request->input('customer_sosmed'),
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'total_nights' => $totalNights,
            'room_price' => $roomPrice,
            'discount' => $discount,
            'admin_discount' => $adminDiscount,
            'total_price' => $totalPrice,
            'status' => $newStatus,
            'expired_at' => $expiredAt,
            'extra_facilities' => $savedExtraFacilities,
        ]);

        return redirect()->route('admin.bookings.index')->with('success', 'Data pemesanan berhasil diperbarui!');
    }

    /**
     * Remove the specified booking from storage (Soft Delete).
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', 'Data pemesanan berhasil dihapus (Soft Delete)!');
    }

    /**
     * Quick update booking status to Lunas (2).
     */
    public function markAsLunas(Booking $booking)
    {
        $booking->update([
            'status' => 2,
            'expired_at' => null,
        ]);

        return redirect()->route('admin.bookings.index')->with('success', 'Status pemesanan ' . $booking->booking_code . ' berhasil diubah menjadi LUNAS!');
    }

    /**
     * Display printable receipt / nota for a booking (Lunas / DP / Selesai).
     */
    public function receipt(Booking $booking)
    {
        $setting = \App\Models\Setting::getSetting();
        return view('admin.bookings.receipt', compact('booking', 'setting'));
    }
}
