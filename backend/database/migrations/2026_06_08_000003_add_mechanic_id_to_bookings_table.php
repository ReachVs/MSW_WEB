<?php

use App\Models\Mechanic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('mechanic_id')->nullable()->after('user_id')->constrained('mechanics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeignIdFor(Mechanic::class);
            $table->dropColumn('mechanic_id');
        });
    }
};
