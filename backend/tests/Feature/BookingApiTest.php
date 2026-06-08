<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\User;
use App\Models\WorkshopSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_booking(): void
    {
        Event::fake([BookingCreated::class]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $startsAt = now()->addDay()->startOfDay()->setTime(8, 0);
        $endsAt = $startsAt->copy()->addHour();

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'Ducati',
            'model' => 'Panigale V4',
            'plate_number' => '1A-1234',
            'engine_capacity' => '1103',
            'service_name' => 'Planning call',
            'customer_name' => 'Casey Client',
            'customer_email' => 'casey@example.com',
            'starts_at' => $startsAt->toISOString(),
            'ends_at' => $endsAt->toISOString(),
            'notes' => 'Bring the project brief.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_name', 'Planning call')
            ->assertJsonPath('data.customer_email', 'casey@example.com');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'customer_email' => 'casey@example.com',
            'status' => Booking::STATUS_PENDING,
        ]);

        Event::assertDispatched(BookingCreated::class);
    }

    public function test_customer_can_cancel_only_pending_or_confirmed_booking(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $booking = Booking::factory()->for($user)->create([
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->putJson("/api/bookings/{$booking->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', Booking::STATUS_CANCELLED);

        $lockedBooking = Booking::factory()->for($user)->create([
            'status' => Booking::STATUS_REPAIR,
        ]);

        $this->putJson("/api/bookings/{$lockedBooking->id}/cancel")
            ->assertStatus(422);
    }

    public function test_admin_can_update_booking_status_with_valid_transition(): void
    {
        $permission = Permission::findOrCreate('manage bookings');
        $adminRole = Role::findOrCreate('admin');
        $adminRole->syncPermissions([$permission]);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        Sanctum::actingAs($admin);

        $booking = Booking::factory()->create([
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->putJson("/api/admin/bookings/{$booking->id}/status", [
            'status' => Booking::STATUS_CONFIRMED,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', Booking::STATUS_CONFIRMED);
    }

    public function test_admin_cannot_skip_invalid_status_transition(): void
    {
        $permission = Permission::findOrCreate('manage bookings');
        $adminRole = Role::findOrCreate('admin');
        $adminRole->syncPermissions([$permission]);

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        Sanctum::actingAs($admin);

        $booking = Booking::factory()->create([
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->putJson("/api/admin/bookings/{$booking->id}/status", [
            'status' => Booking::STATUS_REPAIR,
        ])
            ->assertStatus(422);
    }

    public function test_booking_index_requires_authentication(): void
    {
        Booking::factory()->create();

        $this->getJson('/api/bookings')->assertUnauthorized();
    }

    public function test_available_slots_endpoint_returns_remaining_capacity(): void
    {
        WorkshopSetting::current();
        $date = now()->addDay()->startOfDay();
        $slot = $date->copy()->setTime(8, 0);

        Booking::factory()->count(2)->create([
            'starts_at' => $slot,
            'ends_at' => $slot->copy()->addHour(),
            'status' => Booking::STATUS_PENDING,
        ]);

        $response = $this->getJson('/api/calendar/available-slots?date=' . $date->toDateString());

        $response->assertOk()
            ->assertJsonPath('data.slots.0.time', '08:00')
            ->assertJsonPath('data.slots.0.remaining_capacity', 1);
    }

    public function test_booking_creation_prevents_overbooking_same_slot(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $settings = WorkshopSetting::current();
        $slot = now()->addDays(2)->startOfDay()->setTime(8, 0);

        Booking::factory()->count($settings->max_per_slot)->create([
            'starts_at' => $slot,
            'ends_at' => $slot->copy()->addHour(),
            'status' => Booking::STATUS_PENDING,
        ]);

        $this->postJson('/api/bookings', [
            'bike_name' => 'Ducati',
            'model' => 'Panigale V4',
            'plate_number' => '1A-1234',
            'engine_capacity' => '1103',
            'service_name' => 'Full Motorcycle Wash',
            'selected_services' => [
                ['name' => 'Full Motorcycle Wash', 'price' => 25],
            ],
            'starts_at' => $slot->toISOString(),
        ])->assertStatus(422);
    }
}
