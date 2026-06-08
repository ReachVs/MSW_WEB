<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('operating_start_time', 5)->default('08:00');
            $table->string('operating_end_time', 5)->default('17:00');
            $table->unsignedInteger('max_daily_bookings')->default(20);
            $table->unsignedInteger('max_per_slot')->default(3);
            $table->timestamps();
        });

        DB::table('workshop_settings')->insert([
            'id' => 1,
            'operating_start_time' => '08:00',
            'operating_end_time' => '17:00',
            'max_daily_bookings' => 20,
            'max_per_slot' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_settings');
    }
};
