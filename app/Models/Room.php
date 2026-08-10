<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['code', 'name', 'price', 'weekend_price', 'discount', 'description', 'images'])]
class Room extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }

    /**
     * The facilities that belong to the room.
     */
    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    /**
     * Get the bookings for the room.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the maintenance schedules for the room.
     */
    public function maintenances()
    {
        return $this->hasMany(RoomMaintenance::class);
    }

    /**
     * Calculate weekday price after discount percentage.
     */
    public function getFinalPriceAttribute()
    {
        if ($this->discount && $this->discount > 0) {
            return $this->price - ($this->price * ($this->discount / 100));
        }

        return $this->price;
    }

    /**
     * Calculate weekend/holiday price after discount percentage.
     */
    public function getFinalWeekendPriceAttribute()
    {
        $basePrice = ($this->weekend_price && $this->weekend_price > 0) ? $this->weekend_price : $this->price;
        if ($this->discount && $this->discount > 0) {
            return $basePrice - ($basePrice * ($this->discount / 100));
        }

        return $basePrice;
    }

    /**
     * Calculate total price & nights breakdown for stay dates.
     */
    public function calculateBookingDetails($checkInDate, $checkOutDate)
    {
        $checkIn = \Carbon\Carbon::parse($checkInDate);
        $checkOut = \Carbon\Carbon::parse($checkOutDate);

        $holidayService = app(\App\Services\HolidayService::class);

        $weekdayNights = 0;
        $weekendNights = 0;
        $totalOriginalPrice = 0;
        $totalFinalPrice = 0;

        $current = $checkIn->copy();
        while ($current->lt($checkOut)) {
            $isWeekendOrHoliday = $holidayService->isWeekendOrHoliday($current);
            
            if ($isWeekendOrHoliday) {
                $weekendNights++;
                $base = ($this->weekend_price && $this->weekend_price > 0) ? $this->weekend_price : $this->price;
            } else {
                $weekdayNights++;
                $base = $this->price;
            }

            $discounted = ($this->discount && $this->discount > 0)
                ? $base - ($base * ($this->discount / 100))
                : $base;

            $totalOriginalPrice += $base;
            $totalFinalPrice += $discounted;

            $current->addDay();
        }

        $totalNights = max(1, $weekdayNights + $weekendNights);

        return [
            'total_nights' => $totalNights,
            'weekday_nights' => $weekdayNights,
            'weekend_nights' => $weekendNights,
            'total_original_price' => $totalOriginalPrice,
            'total_final_price' => $totalFinalPrice,
        ];
    }
}
