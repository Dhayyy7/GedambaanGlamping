<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'logo',
    'homestay_name',
    'wa_number',
    'address',
    'gmap_link',
    'media_assets',
])]
class Setting extends Model
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
            'media_assets' => 'array',
        ];
    }

    /**
     * Helper to get singleton setting instance.
     */
    public static function getSetting()
    {
        return static::firstOrCreate(
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
    }
}
