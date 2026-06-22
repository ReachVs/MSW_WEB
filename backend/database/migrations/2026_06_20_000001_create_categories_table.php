<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $categories = [
            ['name' => 'Engine', 'description' => 'Motor components, gaskets, spark plugs, filters and oil'],
            ['name' => 'Brakes', 'description' => 'Brake pads, discs, calipers, lines and brake fluids'],
            ['name' => 'Suspension', 'description' => 'Forks, shock absorbers, seals and linkage kits'],
            ['name' => 'Drivetrain', 'description' => 'Chains, sprockets, belts, clutches and transmission gear'],
            ['name' => 'Electrical', 'description' => 'Batteries, wiring harnesses, starter motors, bulbs and fuses'],
            ['name' => 'Tyres', 'description' => 'Front and rear tyres, inner tubes and valves'],
            ['name' => 'Performance', 'description' => 'Exhaust upgrades, tuning modules, high-flow filters and power kits'],
            ['name' => 'Accessories', 'description' => 'Luggage racks, grips, mirrors, crash bars and cosmetic parts'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat['name'],
                'description' => $cat['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
