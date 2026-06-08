<?php

namespace Database\Seeders;

use App\Models\InventoryPart;
use App\Models\Mechanic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
        ]);

        $manageBookings = Permission::findOrCreate('manage bookings');
        $viewBookings = Permission::findOrCreate('view bookings');

        $adminRole = Role::findOrCreate('admin');
        $adminRole->syncPermissions([$manageBookings, $viewBookings]);

        $customerRole = Role::findOrCreate('customer');
        $customerRole->syncPermissions([$viewBookings]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => 'password'],
        );
        $admin->syncRoles([$adminRole]);

        // Mechanics
        $mechanics = [
            ['name' => 'Marcus Rivera', 'specialization' => 'ECU Performance Tuning', 'status' => 'busy'],
            ['name' => 'Elena Kuznetsova', 'specialization' => 'Suspension & QA', 'status' => 'busy'],
            ['name' => 'Aris Papadopoulos', 'specialization' => 'Supercharger Service', 'status' => 'available'],
            ['name' => 'James Okafor', 'specialization' => 'Brake Systems', 'status' => 'available'],
        ];

        $createdMechanics = [];
        foreach ($mechanics as $m) {
            $createdMechanics[] = Mechanic::firstOrCreate(['name' => $m['name']], $m);
        }

        // Inventory Parts
        $parts = [
            ['name' => 'Brembo Front Caliper GT-S', 'description' => 'Monoblock 6-Piston', 'sku' => 'BR-GT6-2024', 'category' => 'Brakes', 'stock_pct' => 85, 'unit_price_cents' => 284000],
            ['name' => 'Akrapovic Evolution Line', 'description' => 'Titanium Exhaust System', 'sku' => 'AK-EVO-911T', 'category' => 'Engine', 'stock_pct' => 24, 'unit_price_cents' => 1245000],
            ['name' => 'Michelin Pilot Sport Cup 2', 'description' => '305/30 ZR20 (Rear)', 'sku' => 'MC-PS2-R305', 'category' => 'Drivetrain', 'stock_pct' => 60, 'unit_price_cents' => 54000],
            ['name' => 'Carbon Ceramic Rotor Set', 'description' => '410mm Front Axle', 'sku' => 'CC-ROT-410F', 'category' => 'Brakes', 'stock_pct' => 8, 'unit_price_cents' => 890000],
            ['name' => 'Quaife ATB Differential', 'description' => 'Limited Slip Internal', 'sku' => 'QU-ATB-992', 'category' => 'Drivetrain', 'stock_pct' => 92, 'unit_price_cents' => 185000],
            ['name' => 'Brembo Pads Carbon Ceramic', 'description' => 'High-performance brake pads', 'sku' => 'BR-PAD-CC01', 'category' => 'Brakes', 'stock_pct' => 12, 'unit_price_cents' => 48000],
            ['name' => 'Ohlins TTX GP Shock', 'description' => 'Rear suspension unit', 'sku' => 'OH-TTX-GP01', 'category' => 'Suspension', 'stock_pct' => 45, 'unit_price_cents' => 320000],
        ];

        foreach ($parts as $p) {
            InventoryPart::firstOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
