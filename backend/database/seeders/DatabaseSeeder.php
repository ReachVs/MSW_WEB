<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $manageBookings = Permission::findOrCreate('manage bookings');
        $viewBookings = Permission::findOrCreate('view bookings');

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions([$manageBookings, $viewBookings]);

        $customer = Role::findOrCreate('customer');
        $customer->syncPermissions([$viewBookings]);

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
            ],
        );

        $user->syncRoles([$admin]);

        Booking::query()->firstOrCreate(
            [
                'customer_email' => 'client@example.com',
                'starts_at' => now()->addDay()->setHour(10)->setMinute(0)->setSecond(0),
            ],
            [
                'user_id' => $user->id,
                'service_name' => 'Discovery consultation',
                'customer_name' => 'Client Example',
                'ends_at' => now()->addDay()->setHour(11)->setMinute(0)->setSecond(0),
                'status' => 'confirmed',
                'notes' => 'Seed booking for the calendar.',
            ],
        );
    }
}
