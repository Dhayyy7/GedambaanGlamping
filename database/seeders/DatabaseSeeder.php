<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Default Roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Akses penuh ke seluruh sistem dan konfigurasi glamping.',
            ],
            [
                'name' => 'Admin Glamping',
                'slug' => 'admin',
                'description' => 'Pengelolaan unit glamping, pemesanan, dan transaksi.',
            ],
            [
                'name' => 'Staf Resepsionis',
                'slug' => 'staff',
                'description' => 'Pengelolaan reservasi, check-in, dan pelayanan tamu.',
            ],
            [
                'name' => 'Tamu',
                'slug' => 'guest',
                'description' => 'Role dasar untuk pengunjung / pelanggan glamping.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // 2. Seed Super Admin User
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Gedambaan Glamping',
                'username' => 'admin',
                'email' => 'admin@gedambaanglamping.com',
                'password' => Hash::make('password123'),
                'role_user' => 'Super Admin',
            ]
        );

        // 3. Seed Default Glamping Settings
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'logo' => null,
                'homestay_name' => 'Gedambaan Glamping',
                'wa_number' => '08776905151',
                'address' => 'Tepi Pantai Gedambaan, Kotabaru, Kalimantan Selatan',
                'gmap_link' => 'https://maps.google.com/?q=Pantai+Gedambaan+Kotabaru',
                'media_assets' => [],
            ]
        );

        // 4. Seed Glamping Facilities
        $facilities = [
            ['name' => 'Naturehike Premium Tent', 'icon' => 'fa-tent', 'description' => 'Tenda premium berkualitas tinggi dari Naturehike.'],
            ['name' => 'Air Bed', 'icon' => 'fa-mattress-pillow', 'description' => 'Kasur angin empuk untuk kenyamanan tidur.'],
            ['name' => 'Sofabed', 'icon' => 'fa-couch', 'description' => 'Sofa lipat bersantai di dalam tenda.'],
            ['name' => 'Area Bilas & Toilet Dekat', 'icon' => 'fa-toilet', 'description' => 'Fasilitas kamar mandi dan toilet umum yang bersih dan dekat.'],
            ['name' => 'Air Cooler / Kipas Angin', 'icon' => 'fa-fan', 'description' => 'Pendingin udara untuk kenyamanan di siang & malam hari.'],
            ['name' => 'Welcome Drink', 'icon' => 'fa-glass-water', 'description' => 'Minuman penyambutan gratis saat check-in.'],
            ['name' => 'Free Wifi', 'icon' => 'fa-wifi', 'description' => 'Akses internet nirkabel gratis.'],
            ['name' => 'CCTV 24 Jam', 'icon' => 'fa-shield-halved', 'description' => 'Pengawasan keamanan 24 jam.'],
            ['name' => 'BBQ (by Request)', 'icon' => 'fa-fire-burner', 'description' => 'Peralatan BBQ tersedia berdasarkan permintaan.'],
        ];

        $facilityIds = [];
        foreach ($facilities as $facilityData) {
            $facility = Facility::updateOrCreate(
                ['name' => $facilityData['name']],
                $facilityData
            );
            $facilityIds[] = $facility->id;
        }

        // 5. Seed Glamping Units (G1 - G5)
        $units = ['G1', 'G2', 'G3', 'G4', 'G5'];
        foreach ($units as $unitCode) {
            $room = Room::updateOrCreate(
                ['code' => $unitCode],
                [
                    'name' => 'Glamping Unit ' . $unitCode,
                    'price' => 450000,
                    'weekend_price' => 550000,
                    'discount' => 0,
                    'description' => 'Tenda Glamping Premium Naturehike di tepi Pantai Gedambaan Kotabaru dengan pemandangan sunrise yang memukau. Lengkap dengan Air Bed, Sofabed, Air Cooler, Welcome Drink, dan akses pantai langsung.',
                    'images' => [],
                ]
            );

            // Attach facilities to unit
            $room->facilities()->sync($facilityIds);
        }
    }
}

