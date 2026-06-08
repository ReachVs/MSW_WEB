<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('sku')->unique();
            $table->enum('category', ['Brakes', 'Engine', 'Drivetrain', 'Suspension', 'Electrical', 'Other'])->default('Other');
            $table->unsignedTinyInteger('stock_pct')->default(100)->comment('0-100 percentage of stock remaining');
            $table->unsignedInteger('unit_price_cents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_parts');
    }
};
