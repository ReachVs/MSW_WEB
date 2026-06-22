<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\InventoryPart;
use App\Models\Mechanic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_inventory_part(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $response = $this->actingAs($admin)->post('/admin/inventory', [
            'name' => 'Brembo Front Caliper GT-S',
            'description' => 'Monoblock 6-Piston',
            'sku' => 'BR-GT6-2024',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'minimum_stock' => 10,
            'unit_price' => 2840.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_parts', [
            'sku' => 'BR-GT6-2024',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'minimum_stock' => 10,
            'unit_price_cents' => 284000,
        ]);

        // Verifies Stock In log was created
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'Stock In',
            'quantity' => 50,
            'new_stock' => 50,
        ]);
    }

    public function test_admin_can_update_inventory_part_details(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'description' => 'Standard pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'minimum_stock' => 10,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/inventory/{$part->id}", [
            'name' => 'Brembo Pads Pro',
            'description' => 'Upgraded ceramic pads',
            'sku' => 'BR-PAD-01-PRO',
            'category_id' => $brakes->id,
            'stock_qty' => 40, // Stock changed
            'minimum_stock' => 8,
            'unit_price' => 55.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_parts', [
            'id' => $part->id,
            'name' => 'Brembo Pads Pro',
            'sku' => 'BR-PAD-01-PRO',
            'stock_qty' => 40,
            'minimum_stock' => 8,
            'unit_price_cents' => 5500,
        ]);

        // Verifies Adjustment log was created for stock change
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'movement_type' => 'Adjustment',
            'quantity' => -10, // 40 - 50 = -10
            'previous_stock' => 50,
            'new_stock' => 40,
        ]);
    }

    public function test_admin_can_delete_inventory_part(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/inventory/{$part->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('inventory_parts', ['id' => $part->id]);
    }

    public function test_search_filters_inventory_parts(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();
        $drivetrain = Category::where('name', 'Drivetrain')->first();

        InventoryPart::create([
            'name' => 'Brembo Caliper Front',
            'sku' => 'BR-GT-F01',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'unit_price_cents' => 28000,
        ]);

        InventoryPart::create([
            'name' => 'Michelin Pilot Tire',
            'sku' => 'MC-PL-T01',
            'category_id' => $drivetrain->id,
            'stock_qty' => 60,
            'unit_price_cents' => 15000,
        ]);

        // Search for Brembo
        $response = $this->actingAs($admin)->get('/admin/inventory?search=Brembo');
        $response->assertOk();
        $response->assertSee('Brembo Caliper Front');
        $response->assertDontSee('Michelin Pilot Tire');

        // Search for Drivetrain category keyword
        $response = $this->actingAs($admin)->get('/admin/inventory?search=Drivetrain');
        $response->assertOk();
        $response->assertSee('Michelin Pilot Tire');
        $response->assertDontSee('Brembo Caliper Front');
    }

    public function test_category_filter_restricts_inventory_parts(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();
        $drivetrain = Category::where('name', 'Drivetrain')->first();

        InventoryPart::create([
            'name' => 'Brembo Caliper Front',
            'sku' => 'BR-GT-F01',
            'category_id' => $brakes->id,
            'stock_qty' => 50,
            'unit_price_cents' => 28000,
        ]);

        InventoryPart::create([
            'name' => 'Michelin Pilot Tire',
            'sku' => 'MC-PL-T01',
            'category_id' => $drivetrain->id,
            'stock_qty' => 60,
            'unit_price_cents' => 15000,
        ]);

        // Filter by Brakes category
        $response = $this->actingAs($admin)->get('/admin/inventory?category_id='.$brakes->id);
        $response->assertOk();
        $response->assertSee('Brembo Caliper Front');
        $response->assertDontSee('Michelin Pilot Tire');

        // Filter by Drivetrain category
        $response = $this->actingAs($admin)->get('/admin/inventory?category_id='.$drivetrain->id);
        $response->assertOk();
        $response->assertSee('Michelin Pilot Tire');
        $response->assertDontSee('Brembo Caliper Front');
    }

    public function test_admin_can_stock_in(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 10,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->post("/admin/inventory/parts/{$part->id}/stock-in", [
            'quantity' => 15,
            'remarks' => 'Restock from Brembo distributor',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_parts', [
            'id' => $part->id,
            'stock_qty' => 25,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'movement_type' => 'Stock In',
            'quantity' => 15,
            'previous_stock' => 10,
            'new_stock' => 25,
            'remarks' => 'Restock from Brembo distributor',
        ]);
    }

    public function test_admin_can_stock_out(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 20,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->post("/admin/inventory/parts/{$part->id}/stock-out", [
            'quantity' => 5,
            'remarks' => 'Manual dispatch to service bay',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_parts', [
            'id' => $part->id,
            'stock_qty' => 15,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'movement_type' => 'Stock Out',
            'quantity' => -5,
            'previous_stock' => 20,
            'new_stock' => 15,
            'remarks' => 'Manual dispatch to service bay',
        ]);
    }

    public function test_admin_cannot_stock_out_exceeding_current_stock(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 5,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->post("/admin/inventory/parts/{$part->id}/stock-out", [
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertEquals(5, $part->fresh()->stock_qty);
    }

    public function test_admin_can_adjust_stock_level(): void
    {
        $admin = $this->createAdminUser();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Pads',
            'sku' => 'BR-PAD-01',
            'category_id' => $brakes->id,
            'stock_qty' => 10,
            'unit_price_cents' => 4500,
        ]);

        $response = $this->actingAs($admin)->post("/admin/inventory/parts/{$part->id}/adjust", [
            'quantity' => 8,
            'remarks' => 'Auditing correction: discrepancy of 2 units found during annual count',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_parts', [
            'id' => $part->id,
            'stock_qty' => 8,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $part->id,
            'movement_type' => 'Adjustment',
            'quantity' => -2,
            'previous_stock' => 10,
            'new_stock' => 8,
            'remarks' => 'Auditing correction: discrepancy of 2 units found during annual count',
        ]);
    }

    public function test_booking_completion_auto_deducts_inventory(): void
    {
        $admin = $this->createAdminUser();
        $engineCat = Category::where('name', 'Engine')->first();

        $oilPart = InventoryPart::create([
            'name' => 'Engine Oil 10W40',
            'sku' => 'ENG-OIL-10W40',
            'category_id' => $engineCat->id,
            'stock_qty' => 50,
            'unit_price_cents' => 1500,
        ]);

        $filterPart = InventoryPart::create([
            'name' => 'Oil Filter HF138',
            'sku' => 'ENG-FLT-HF138',
            'category_id' => $engineCat->id,
            'stock_qty' => 20,
            'unit_price_cents' => 800,
        ]);

        $mechanic = Mechanic::create([
            'name' => 'Marcus Rivera',
            'specialization' => 'General tuning',
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'customer_name' => 'Test Customer',
            'customer_email' => 'cust@test.com',
            'bike_name' => 'Honda CBR',
            'model' => '600RR',
            'plate_number' => 'CBR-600',
            'starts_at' => now(),
            'ends_at' => now()->addHour(),
            'service_name' => 'Standard Oil Change Service',
            'status' => Booking::STATUS_READY_PICKUP,
            'mechanic_id' => $mechanic->id,
            'total_amount' => 120.00,
        ]);

        // Complete the service booking
        $response = $this->actingAs($admin)->patch("/admin/bookings/{$booking->id}/status", [
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $response->assertRedirect();
        $this->assertEquals(Booking::STATUS_COMPLETED, $booking->fresh()->status);

        // Verify quantities were deducted: 50 -> 48, 20 -> 19
        $this->assertEquals(48, $oilPart->fresh()->stock_qty);
        $this->assertEquals(19, $filterPart->fresh()->stock_qty);

        // Verify stock movement logs were written
        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $oilPart->id,
            'movement_type' => 'Service Usage',
            'quantity' => -2,
            'previous_stock' => 50,
            'new_stock' => 48,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'part_id' => $filterPart->id,
            'movement_type' => 'Service Usage',
            'quantity' => -1,
            'previous_stock' => 20,
            'new_stock' => 19,
        ]);
    }

    public function test_non_admin_cannot_access_inventory(): void
    {
        $user = User::factory()->create();
        $brakes = Category::where('name', 'Brakes')->first();

        $part = InventoryPart::create([
            'name' => 'Brembo Caliper Front',
            'sku' => 'BR-GT-F01',
            'category_id' => $brakes->id,
            'stock_qty' => 8,
            'unit_price_cents' => 28000,
        ]);

        $this->actingAs($user)->get('/admin/inventory')->assertStatus(403);
        $this->actingAs($user)->post('/admin/inventory', [])->assertStatus(403);
        $this->actingAs($user)->patch("/admin/inventory/{$part->id}", [])->assertStatus(403);
        $this->actingAs($user)->delete("/admin/inventory/{$part->id}")->assertStatus(403);
    }

    private function createAdminUser(): User
    {
        $adminRole = Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        return $admin;
    }
}
