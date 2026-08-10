<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * Cache key prefix for holiday dates.
     */
    protected const CACHE_KEY = 'indonesia_national_holidays_';

    /**
     * Get array of national holiday date strings (Y-m-d) for a given year.
     *
     * @param int|null $year
     * @return array<string> Array of date strings 'YYYY-MM-DD'
     */
    public function getHolidayDates(?int $year = null): array
    {
        $year = $year ?? (int) date('Y');
        $cacheKey = self::CACHE_KEY . $year;

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($year) {
            return $this->syncHolidaysFromApi($year);
        });
    }

    /**
     * Clear cached holiday dates for a year.
     */
    public function clearCache(?int $year = null): void
    {
        $year = $year ?? (int) date('Y');
        Cache::forget(self::CACHE_KEY . $year);
    }

    /**
     * Seed default holidays & sync from public Indonesian holiday API to DB.
     *
     * @param int $year
     * @return array<string>
     */
    public function syncHolidaysFromApi(int $year): array
    {
        // 1. Seed built-in standard Indonesian national holidays
        $defaultHolidays = $this->getDefaultHolidays($year);
        foreach ($defaultHolidays as $dateStr => $name) {
            Holiday::updateOrCreate(
                ['date' => $dateStr],
                ['name' => $name, 'is_national_holiday' => true]
            );
        }

        // 2. Try fetching external API for live updates
        try {
            $response = Http::timeout(4)->get("https://dayoffapi.vercel.app/api?year={$year}");
            
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (isset($item['is_cuti']) && !$item['is_cuti']) {
                            $dateStr = Carbon::parse($item['tanggal'])->format('Y-m-d');
                            $name = $item['keterangan'] ?? 'Hari Libur Nasional';
                            
                            Holiday::updateOrCreate(
                                ['date' => $dateStr],
                                ['name' => $name, 'is_national_holiday' => true]
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info("Holiday API fetch info for year {$year}: Using default built-in national holidays.");
        }

        // 3. Return all holiday dates from DB for this year
        return Holiday::whereYear('date', $year)
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Get default built-in national holidays dictionary for a given year.
     *
     * @param int $year
     * @return array<string, string> Key: 'YYYY-MM-DD', Value: Description
     */
    public function getDefaultHolidays(int $year): array
    {
        $defaults = [
            "{$year}-01-01" => "Tahun Baru {$year} Masehi",
            "{$year}-05-01" => "Hari Buruh Internasional",
            "{$year}-06-01" => "Hari Lahir Pancasila",
            "{$year}-08-17" => "Hari Kemerdekaan Republik Indonesia",
            "{$year}-12-25" => "Hari Raya Natal",
        ];

        if ($year === 2026) {
            $defaults = array_merge($defaults, [
                '2026-01-16' => 'Isra Mikraj Nabi Muhammad SAW',
                '2026-02-17' => 'Tahun Baru Imlek 2577 Kongzili',
                '2026-03-19' => 'Hari Raya Nyepi Tahun Baru Saka 1948',
                '2026-03-20' => 'Hari Raya Idul Fitri 1447 Hijriah',
                '2026-03-21' => 'Hari Raya Idul Fitri 1447 Hijriah',
                '2026-04-03' => 'Wafat Yesus Kristus',
                '2026-04-05' => 'Kebangkitan Yesus Kristus (Paskah)',
                '2026-05-14' => 'Kenaikan Yesus Kristus',
                '2026-05-27' => 'Hari Raya Waisak 2570 BE & Idul Adha 1447H',
                '2026-06-16' => 'Tahun Baru Islam 1448 Hijriah',
                '2026-08-25' => 'Maulid Nabi Muhammad SAW',
            ]);
        } elseif ($year === 2025) {
            $defaults = array_merge($defaults, [
                '2025-01-27' => 'Isra Mikraj Nabi Muhammad SAW',
                '2025-01-29' => 'Tahun Baru Imlek 2576 Kongzili',
                '2025-03-29' => 'Hari Raya Nyepi Tahun Baru Saka 1947',
                '2025-03-31' => 'Hari Raya Idul Fitri 1446 Hijriah',
                '2025-04-01' => 'Hari Raya Idul Fitri 1446 Hijriah',
                '2025-04-18' => 'Wafat Yesus Kristus',
                '2025-04-20' => 'Kebangkitan Yesus Kristus (Paskah)',
                '2025-05-12' => 'Hari Raya Waisak 2569 BE',
                '2025-05-29' => 'Kenaikan Yesus Kristus',
                '2025-06-06' => 'Hari Raya Idul Adha 1446 Hijriah',
                '2025-06-27' => 'Tahun Baru Islam 1447 Hijriah',
                '2025-09-05' => 'Maulid Nabi Muhammad SAW',
            ]);
        }

        return $defaults;
    }

    /**
     * Check if a given date is a Weekend (Friday, Saturday, Sunday) OR a National Holiday.
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function isWeekendOrHoliday($date): bool
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Day of week: 5 = Friday, 6 = Saturday (Jumat & Sabtu)
        $dayOfWeek = $carbon->dayOfWeek;
        if (in_array($dayOfWeek, [5, 6])) {
            return true;
        }

        // Check if date is in national holiday list
        $holidayDates = $this->getHolidayDates((int) $carbon->year);
        return in_array($carbon->format('Y-m-d'), $holidayDates);
    }
}
