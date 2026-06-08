<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('bike_name')->nullable()->after('user_id');
            $table->string('model')->nullable()->after('bike_name');
            $table->string('plate_number')->nullable()->after('model');
            $table->string('engine_capacity')->nullable()->after('plate_number');
            $table->unsignedBigInteger('service_id')->nullable()->after('engine_capacity');
            
            // Make existing fields nullable as they might not be sent from the new frontend flow
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_email')->nullable()->change();
            $table->dateTime('starts_at')->nullable()->change();
            $table->dateTime('ends_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['bike_name', 'model', 'plate_number', 'engine_capacity', 'service_id']);
            
            $table->string('customer_name')->nullable(false)->change();
            $table->string('customer_email')->nullable(false)->change();
            $table->dateTime('starts_at')->nullable(false)->change();
            $table->dateTime('ends_at')->nullable(false)->change();
        });
    }
};
