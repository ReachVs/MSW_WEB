<?php

namespace App\Models;

use App\Enums\MechanicStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialization',
        'status',
    ];

    protected $casts = [
        'status' => MechanicStatus::class,
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
