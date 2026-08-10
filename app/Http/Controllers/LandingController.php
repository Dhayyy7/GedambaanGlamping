<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ExtraFacility;
use App\Models\Room;
use App\Models\RoomMaintenance;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        $setting = Setting::getSetting();

        $rooms = Room::with('facilities')->latest()->get();
        $extraFacilities = ExtraFacility::all();

        return view('landing.index', compact('setting', 'rooms', 'extraFacilities'));
    }

    /**
     * Display real-time availability calendar for all rooms/units.
     */
    public function checkRoom(Request $request)
    {
        $setting = Setting::getSetting();

        $calMonth = sprintf('%02d', (int) $request->input('cal_month', date('m')));
        $calYear = (int) $request->input('cal_year', date('Y'));

        $rooms = Room::with(['facilities', 'maintenances', 'bookings' => function($q) {
            $q->whereIn('status', [1, 2, 3, 4]);
        }])->latest()->get();

        $holidayService = app(\App\Services\HolidayService::class);
        $holidayDates = $holidayService->getHolidayDates($calYear);

        return view('landing.check-room', compact('rooms', 'setting', 'holidayDates', 'calMonth', 'calYear'));
    }

    /**
     * Display the room booking detail page.
     */
    public function booking(Room $room)
    {
        $room->load(['facilities', 'maintenances', 'bookings']);

        $setting = Setting::getSetting();

        $extraFacilities = ExtraFacility::all();

        // Calculate booked dates for availability calendar
        $bookedDates = [];
        foreach ($room->bookings as $b) {
            if (in_array($b->status, [1, 2, 3, 4]) && $b->check_in_date && $b->check_out_date) {
                $curr = Carbon::parse($b->check_in_date);
                $end = Carbon::parse($b->check_out_date);
                while ($curr < $end) {
                    $bookedDates[] = $curr->format('Y-m-d');
                    $curr->addDay();
                }
            }
        }

        // Calculate maintenance dates
        $maintenanceDates = [];
        foreach ($room->maintenances as $m) {
            if ($m->start_date && $m->end_date) {
                $curr = Carbon::parse($m->start_date);
                $end = Carbon::parse($m->end_date);
                while ($curr <= $end) {
                    $maintenanceDates[] = $curr->format('Y-m-d');
                    $curr->addDay();
                }
            }
        }

        // Get National Holiday Dates (Current & Next Year)
        $holidayService = app(\App\Services\HolidayService::class);
        $currentYear = (int) date('Y');
        $holidayDates = array_values(array_unique(array_merge(
            $holidayService->getHolidayDates($currentYear),
            $holidayService->getHolidayDates($currentYear + 1)
        )));

        return view('landing.booking', compact('setting', 'room', 'extraFacilities', 'bookedDates', 'maintenanceDates', 'holidayDates'));
    }

    /**
     * Store new booking from landing/booking modal (Status 1: Pending).
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'extra_facility_ids' => ['nullable', 'array'],
            'extra_facility_ids.*' => ['exists:extra_facilities,id'],
        ]);

        $roomId = $validated['room_id'];
        $checkInStr = $validated['check_in_date'];
        $checkOutStr = $validated['check_out_date'];

        // Check availability conflict with active bookings (status 1, 2, 3, 4)
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
            return response()->json([
                'success' => false,
                'message' => 'Kamar sudah terbooking pada tanggal yang dipilih. Silakan pilih tanggal lain.'
            ], 422);
        }

        // Check maintenance conflict
        $isMaintenanceConflict = RoomMaintenance::where('room_id', $roomId)
            ->where(function ($q) use ($checkInStr, $checkOutStr) {
                $q->where('start_date', '<', $checkOutStr)
                  ->where('end_date', '>=', $checkInStr);
            })
            ->exists();

        if ($isMaintenanceConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Kamar sedang dalam masa pemeliharaan (maintenance) pada tanggal yang dipilih. Silakan pilih tanggal lain.'
            ], 422);
        }

        $room = Room::findOrFail($roomId);
        $checkIn = Carbon::parse($checkInStr);
        $checkOut = Carbon::parse($checkOutStr);

        // Calculate dynamic stay details (weekday vs weekend/holiday)
        $stayDetails = $room->calculateBookingDetails($checkInStr, $checkOutStr);
        $totalNights = $stayDetails['total_nights'];
        $roomTotalPrice = $stayDetails['total_final_price'];

        // Generate Booking Code: RoomCode + YYYYMMDD
        $baseCode = strtoupper($room->code) . $checkIn->format('Ymd');
        $bookingCode = $baseCode;
        $counter = 1;
        while (Booking::withTrashed()->where('booking_code', $bookingCode)->exists()) {
            $bookingCode = $baseCode . '-' . $counter;
            $counter++;
        }

        $roomPrice = $room->price;
        $discount = $room->discount;

        // Process Extra Facilities
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

        // Status 1 = Pending, Expired in 2 hours
        $booking = Booking::create([
            'room_id' => $room->id,
            'booking_code' => $bookingCode,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'] ?? 'Banjarbaru',
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'total_nights' => $totalNights,
            'room_price' => $roomPrice,
            'discount' => $discount,
            'total_price' => $totalPrice,
            'status' => 1,
            'expired_at' => Carbon::now()->addHours(2),
            'extra_facilities' => $savedExtraFacilities,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dicatat dengan status Pending',
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'room_name' => $room->name,
                'room_code' => $room->code,
                'check_in_date' => $checkIn->format('Y-m-d'),
                'check_out_date' => $checkOut->format('Y-m-d'),
                'total_nights' => $totalNights,
                'total_price' => $totalPrice,
                'extra_facilities' => $savedExtraFacilities,
            ]
        ]);
    }
}
