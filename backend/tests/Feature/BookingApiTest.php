<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\Mechanic;
use App\Models\Service;
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
        $service = $this->createService([
            'price' => 75.00,
        ]);

        $startsAt = now()->addDay()->startOfDay()->setTime(8, 0);
        $endsAt = $startsAt->copy()->addHour();

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'Ducati',
            'model' => 'Panigale V4',
            'plate_number' => '1A-1234',
            'engine_capacity' => '1103',
            'service_id' => $service->id,
            'service_name' => 'Planning call',
            'selected_services' => [
                ['id' => $service->id],
            ],
            'customer_name' => 'Casey Client',
            'customer_email' => 'casey@example.com',
            'starts_at' => $startsAt->toISOString(),
            'ends_at' => $endsAt->toISOString(),
            'notes' => 'Bring the project brief.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_name', $service->name)
            ->assertJsonPath('data.customer_email', 'casey@example.com')
            ->assertJsonPath('data.total_amount', 75);

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

        $response = $this->getJson('/api/calendar/available-slots?date='.$date->toDateString());

        $response->assertOk()
            ->assertJsonPath('data.slots.0.time', '08:00')
            ->assertJsonPath('data.slots.0.remaining_capacity', 1);
    }

    public function test_booking_creation_prevents_overbooking_same_slot(): void
    {
        $user = User::factory()->create();
        $service = $this->createService([
            'price' => 25.00,
        ]);
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
            'service_id' => $service->id,
            'service_name' => 'Full Motorcycle Wash',
            'selected_services' => [
                ['id' => $service->id],
            ],
            'starts_at' => $slot->toISOString(),
        ])->assertStatus(422);
    }

    public function test_booking_creation_ignores_client_supplied_service_prices_and_total(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $service = $this->createService([
            'name' => 'Trusted Service',
            'price' => 180.00,
            'main_category' => 'maintenance',
            'selection_mode' => 1,
        ]);

        $startsAt = now()->addDays(2)->startOfDay()->setTime(9, 0);

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'BMW',
            'model' => 'S1000RR',
            'plate_number' => 'QA-180',
            'engine_capacity' => '1000',
            'service_id' => $service->id,
            'service_name' => 'Fake Discount',
            'selected_services' => [
                [
                    'id' => $service->id,
                    'name' => 'Fake Discount',
                    'price' => 1,
                ],
            ],
            'total_amount' => 1,
            'starts_at' => $startsAt->toISOString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_name', 'Trusted Service')
            ->assertJsonPath('data.total_amount', 180)
            ->assertJsonPath('data.selected_services.0.price', 180);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'total_amount' => 180.00,
        ]);
    }

    public function test_admin_can_create_walkin_booking_with_washing_package(): void
    {
        $adminRole = Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $package = Service::create([
            'code' => 'WASH_TEST_PKG',
            'name' => 'Test Wash Package',
            'description' => 'Test package',
            'category' => 'washing',
            'main_category' => 'washing',
            'sub_category' => 'Test Wash Package',
            'parent_id' => null,
            'selection_mode' => 0,
            'price' => null,
            'is_active' => true,
        ]);

        Service::create([
            'code' => 'WASH_TEST_ITEM_1',
            'name' => 'Item 1',
            'price' => 30.00,
            'category' => 'washing',
            'main_category' => 'washing',
            'sub_category' => 'Test Wash Package',
            'parent_id' => $package->id,
            'selection_mode' => 1,
            'is_active' => true,
        ]);

        Service::create([
            'code' => 'WASH_TEST_ITEM_2',
            'name' => 'Item 2',
            'price' => 45.00,
            'category' => 'washing',
            'main_category' => 'washing',
            'sub_category' => 'Test Wash Package',
            'parent_id' => $package->id,
            'selection_mode' => 1,
            'is_active' => true,
        ]);

        $startsAt = now()->addDays(2)->startOfDay()->setTime(8, 0);

        $response = $this->post('/admin/bookings', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'bike_name' => 'Yamaha',
            'model' => 'R1',
            'plate_number' => 'XYZ-999',
            'engine_capacity' => '998',
            'service_ids' => [$package->id],
            'booking_date' => $startsAt->toDateString(),
            'booking_time' => '08:00',
            'status' => Booking::STATUS_CONFIRMED,
            'notes' => 'Walk-in booking test',
        ]);

        $response->assertRedirect(route('admin.queue'));

        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'John Doe',
            'service_name' => 'Test Wash Package',
            'total_amount' => 75.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $booking = Booking::where('customer_name', 'John Doe')->first();
        $this->assertNotNull($booking);
        $selectedServices = $booking->selected_services;
        $this->assertCount(1, $selectedServices);
        $this->assertEquals('Test Wash Package', $selectedServices[0]['name']);
        $this->assertEquals(75.00, $selectedServices[0]['price']);
    }

    public function test_booking_resource_includes_category_attributes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Booking::factory()->for($user)->create([
            'service_name' => 'Full Motorcycle Wash',
            'selected_services' => [
                [
                    'id' => 46,
                    'name' => 'Full Motorcycle Wash',
                    'price' => 25,
                    'category' => 'washing',
                    'main_category' => 'washing',
                ],
            ],
            'total_amount' => 25.00,
        ]);

        $this->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonPath('data.0.category_key', 'washing')
            ->assertJsonPath('data.0.main_category_label', 'Washing Services')
            ->assertJsonPath('data.0.main_category_icon', 'wash')
            ->assertJsonPath('data.0.image_url', '/motorcycle-wash.png');
    }

    public function test_admin_can_create_walkin_booking_in_the_past(): void
    {
        $adminRole = Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $service = Service::create([
            'code' => 'PAST_TEST_SVC',
            'name' => 'Past Service',
            'description' => 'Service in past test',
            'category' => 'maintenance',
            'main_category' => 'maintenance',
            'sub_category' => null,
            'parent_id' => null,
            'selection_mode' => 0,
            'price' => 50.00,
            'is_active' => true,
        ]);

        // 2 days ago at 10:00 AM
        $startsAt = now()->subDays(2)->startOfDay()->setTime(10, 0);

        $response = $this->post('/admin/bookings', [
            'customer_name' => 'Past Customer',
            'customer_email' => 'past@example.com',
            'bike_name' => 'Honda',
            'model' => 'CB400',
            'plate_number' => 'ABC-123',
            'engine_capacity' => '400',
            'service_ids' => [$service->id],
            'booking_date' => $startsAt->toDateString(),
            'booking_time' => '10:00',
            'status' => Booking::STATUS_CONFIRMED,
            'notes' => 'Walk-in booking in the past',
        ]);

        $response->assertRedirect(route('admin.queue'));

        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Past Customer',
            'service_name' => 'Past Service',
            'total_amount' => 50.00,
            'status' => Booking::STATUS_CONFIRMED,
            'starts_at' => $startsAt->toDateTimeString(),
        ]);
    }

    public function test_admin_can_delete_mechanic(): void
    {
        $adminRole = Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin);

        $mechanic = Mechanic::create([
            'name' => 'Delete Test Mechanic',
            'specialization' => 'Tuning',
            'status' => 'available',
        ]);

        $booking = Booking::factory()->create([
            'mechanic_id' => $mechanic->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->delete("/admin/mechanics/{$mechanic->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('mechanics', ['id' => $mechanic->id]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'mechanic_id' => null,
        ]);
    }

    public function test_non_admin_cannot_delete_mechanic(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mechanic = Mechanic::create([
            'name' => 'Secure Test Mechanic',
            'specialization' => 'Tuning',
            'status' => 'available',
        ]);

        $response = $this->delete("/admin/mechanics/{$mechanic->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('mechanics', ['id' => $mechanic->id]);
    }

    public function test_complete_lifecycle_customer_to_admin_to_mechanic_flow(): void
    {
        // 1. Customer registers / authenticates, chooses services and books a slot.
        $customer = User::factory()->create();
        $adminRole = Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $service = $this->createService([
            'price' => 100.00,
        ]);

        $mechanic = Mechanic::create([
            'name' => 'Marcus Rivera',
            'specialization' => 'Washing',
            'status' => 'available',
        ]);

        Sanctum::actingAs($customer);

        $startsAt = now()->addDays(2)->startOfDay()->setTime(10, 0);
        $endsAt = $startsAt->copy()->addHour();

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'Ducati',
            'model' => 'Monster 821',
            'plate_number' => 'TST-8888',
            'engine_capacity' => '821',
            'service_id' => $service->id,
            'service_name' => $service->name,
            'selected_services' => [
                ['id' => $service->id],
            ],
            'starts_at' => $startsAt->toISOString(),
            'ends_at' => $endsAt->toISOString(),
            'customer_name' => 'Deep Tester',
            'customer_email' => 'deep.tester@example.com',
        ]);

        $response->assertCreated();
        $bookingId = $response->json('data.id');

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_PENDING,
        ]);

        // 2. Admin logs in, views the pending booking, assigns a mechanic and confirms it.
        Sanctum::actingAs($admin);

        // Verify admin can see it in admin booking list
        $this->getJson('/api/admin/bookings')
            ->assertOk()
            ->assertJsonFragment(['id' => $bookingId, 'status' => Booking::STATUS_PENDING]);

        // Assign mechanic
        $this->actingAs($admin)->patch(route('admin.bookings.mechanic', $bookingId), [
            'mechanic_id' => $mechanic->id,
        ])->assertRedirect();

        // Confirm booking
        $this->actingAs($admin)->patch(route('admin.bookings.status', $bookingId), [
            'status' => Booking::STATUS_CONFIRMED,
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_CONFIRMED,
            'mechanic_id' => $mechanic->id,
        ]);

        // 3. Mechanic accesses the mechanic queue/portal and updates status to 'repair'.
        $this->patch(route('mechanic.bookings.status', $bookingId), [
            'status' => Booking::STATUS_REPAIR,
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_REPAIR,
        ]);

        // Mechanic advances to 'ready_pickup'
        $this->patch(route('mechanic.bookings.status', $bookingId), [
            'status' => Booking::STATUS_READY_PICKUP,
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_READY_PICKUP,
        ]);

        // 4. Admin completes the booking
        $this->actingAs($admin)->patch(route('admin.bookings.status', $bookingId), [
            'status' => Booking::STATUS_COMPLETED,
        ])->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // 5. Customer checks active and history bookings
        Sanctum::actingAs($customer);

        // Active bookings should NOT contain this completed booking
        $this->getJson('/api/bookings/active')
            ->assertOk()
            ->assertJsonMissing([['id' => $bookingId]]);

        // History bookings SHOULD contain this completed booking
        $this->getJson('/api/bookings/history')
            ->assertOk()
            ->assertJsonFragment(['id' => $bookingId, 'status' => Booking::STATUS_COMPLETED]);
    }

    public function test_customer_profile_saves_phone(): void
    {
        $user = User::factory()->create(['phone' => null]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/auth/me', [
            'name' => 'Updated Name',
            'phone' => '123-456-7890',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.phone', '123-456-7890');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '123-456-7890',
        ]);
    }

    public function test_booking_creation_maps_phone_from_profile_if_missing(): void
    {
        Event::fake([BookingCreated::class]);

        $user = User::factory()->create(['phone' => '999-999-9999']);
        Sanctum::actingAs($user);
        $service = $this->createService();

        $startsAt = now()->addDay()->startOfDay()->setTime(8, 0);

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'Honda',
            'model' => 'CBR',
            'plate_number' => '11-222',
            'service_id' => $service->id,
            'service_name' => $service->name,
            'selected_services' => [
                ['id' => $service->id],
            ],
            'customer_name' => 'Reach User',
            'customer_email' => 'reach@example.com',
            'starts_at' => $startsAt->toISOString(),
            'notes' => 'None',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.customer_phone', '999-999-9999');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'customer_phone' => '999-999-9999',
        ]);
    }

    public function test_booking_creation_retains_provided_customer_phone(): void
    {
        Event::fake([BookingCreated::class]);

        $user = User::factory()->create(['phone' => '999-999-9999']);
        Sanctum::actingAs($user);
        $service = $this->createService();

        $startsAt = now()->addDay()->startOfDay()->setTime(8, 0);

        $response = $this->postJson('/api/bookings', [
            'bike_name' => 'Honda',
            'model' => 'CBR',
            'plate_number' => '11-222',
            'service_id' => $service->id,
            'service_name' => $service->name,
            'selected_services' => [
                ['id' => $service->id],
            ],
            'customer_name' => 'Reach User',
            'customer_email' => 'reach@example.com',
            'customer_phone' => '111-111-1111',
            'starts_at' => $startsAt->toISOString(),
            'notes' => 'None',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.customer_phone', '111-111-1111');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'customer_phone' => '111-111-1111',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createService(array $attributes = []): Service
    {
        static $serviceSequence = 1;

        $defaults = [
            'code' => 'TEST_SERVICE_'.$serviceSequence,
            'name' => 'Test Service '.$serviceSequence,
            'description' => 'Test service description',
            'price' => 50.00,
            'category' => 'maintenance',
            'main_category' => 'maintenance',
            'sub_category' => null,
            'parent_id' => null,
            'selection_mode' => 1,
            'is_active' => true,
        ];

        $serviceSequence++;

        return Service::create(array_merge($defaults, $attributes));
    }
}
