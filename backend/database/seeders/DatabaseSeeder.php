<?php

namespace Database\Seeders;

use App\Models\Category;
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
        $brakesId = Category::where('name', 'Brakes')->value('id');
        $engineId = Category::where('name', 'Engine')->value('id');
        $drivetrainId = Category::where('name', 'Drivetrain')->value('id');
        $suspensionId = Category::where('name', 'Suspension')->value('id');

        $parts = [
            ['name' => 'Brembo Front Caliper GT-S', 'description' => 'Monoblock 6-Piston', 'sku' => 'BR-GT6-2024', 'category_id' => $brakesId, 'stock_qty' => 85, 'minimum_stock' => 10, 'status' => 'active', 'unit_price_cents' => 284000],
            ['name' => 'Akrapovic Evolution Line', 'description' => 'Titanium Exhaust System', 'sku' => 'AK-EVO-911T', 'category_id' => $engineId, 'stock_qty' => 24, 'minimum_stock' => 5, 'status' => 'active', 'unit_price_cents' => 1245000],
            ['name' => 'Michelin Pilot Sport Cup 2', 'description' => '305/30 ZR20 (Rear)', 'sku' => 'MC-PS2-R305', 'category_id' => $drivetrainId, 'stock_qty' => 60, 'minimum_stock' => 12, 'status' => 'active', 'unit_price_cents' => 54000],
            ['name' => 'Carbon Ceramic Rotor Set', 'description' => '410mm Front Axle', 'sku' => 'CC-ROT-410F', 'category_id' => $brakesId, 'stock_qty' => 8, 'minimum_stock' => 10, 'status' => 'active', 'unit_price_cents' => 890000],
            ['name' => 'Quaife ATB Differential', 'description' => 'Limited Slip Internal', 'sku' => 'QU-ATB-992', 'category_id' => $drivetrainId, 'stock_qty' => 92, 'minimum_stock' => 15, 'status' => 'active', 'unit_price_cents' => 185000],
            ['name' => 'Brembo Pads Carbon Ceramic', 'description' => 'High-performance brake pads', 'sku' => 'BR-PAD-CC01', 'category_id' => $brakesId, 'stock_qty' => 12, 'minimum_stock' => 15, 'status' => 'active', 'unit_price_cents' => 48000],
            ['name' => 'Ohlins TTX GP Shock', 'description' => 'Rear suspension unit', 'sku' => 'OH-TTX-GP01', 'category_id' => $suspensionId, 'stock_qty' => 45, 'minimum_stock' => 8, 'status' => 'active', 'unit_price_cents' => 320000],
            ['name' => 'Engine Oil 10W40', 'description' => 'Premium synthetic motorcycle engine oil', 'sku' => 'ENG-OIL-10W40', 'category_id' => $engineId, 'stock_qty' => 100, 'minimum_stock' => 20, 'status' => 'active', 'unit_price_cents' => 1550],
            ['name' => 'Oil Filter HF138', 'description' => 'High performance premium oil filter', 'sku' => 'ENG-FLT-HF138', 'category_id' => $engineId, 'stock_qty' => 50, 'minimum_stock' => 10, 'status' => 'active', 'unit_price_cents' => 890],
        ];

        foreach ($parts as $p) {
            InventoryPart::firstOrCreate(['sku' => $p['sku']], $p);
        }
    }
}
