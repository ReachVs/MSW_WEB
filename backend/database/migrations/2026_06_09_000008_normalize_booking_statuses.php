<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')->where('status', 'queued')->update([
            'status' => Booking::STATUS_PENDING,
        ]);

        DB::table('bookings')->where('status', 'in_service')->update([
            'status' => Booking::STATUS_REPAIR,
        ]);

        DB::table('bookings')->where('status', 'qa')->update([
            'status' => Booking::STATUS_READY_PICKUP,
        ]);
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', Booking::STATUS_PENDING)->update([
            'status' => 'queued',
        ]);

        DB::table('bookings')->where('status', Booking::STATUS_REPAIR)->update([
            'status' => 'in_service',
        ]);

        DB::table('bookings')->where('status', Booking::STATUS_READY_PICKUP)->update([
            'status' => 'qa',
        ]);
    }
};
