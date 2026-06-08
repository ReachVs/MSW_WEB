<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopSetting extends Model
{
    protected $fillable = [
        'operating_start_time',
        'operating_end_time',
        'max_daily_bookings',
        'max_per_slot',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'operating_start_time' => '08:00',
                'operating_end_time' => '17:00',
                'max_daily_bookings' => 20,
                'max_per_slot' => 3,
            ],
        );
    }
}
