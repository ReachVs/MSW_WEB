<?php

namespace Tests\Feature;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_booking(): void
    {
        Event::fake([BookingCreated::class]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $startsAt = now()->addDay()->setSecond(0);
        $endsAt = $startsAt->copy()->addHour();

        $response = $this->postJson('/api/bookings', [
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
            'status' => 'confirmed',
        ]);

        Event::assertDispatched(BookingCreated::class);
    }

    public function test_booking_index_requires_authentication(): void
    {
        Booking::factory()->create();

        $this->getJson('/api/bookings')->assertUnauthorized();
    }
}
