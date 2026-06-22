<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\InventoryPart;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class InventoryIntegrator
{
    /**
     * Deduct inventory quantities and create stock movement logs for parts used in a completed booking.
     */
    public static function deductForBooking(Booking $booking): void
    {
        Log::info("InventoryIntegrator: Processing booking #{$booking->id} status updated to completed.");

        $partsToDeduct = [];

        // 1. Resolve parts by matching service name keywords
        $serviceName = strtolower($booking->service_name);

        // Oil Change Service uses:
        // - Engine Oil x2
        // - Oil Filter x1
        if (str_contains($serviceName, 'oil change')) {
            self::addPartToDeduct($partsToDeduct, 'ENG-OIL-10W40', 2);
            self::addPartToDeduct($partsToDeduct, 'ENG-FLT-HF138', 1);
        }

        if (str_contains($serviceName, 'brake')) {
            self::addPartToDeduct($partsToDeduct, 'BR-PAD-CC01', 1);
        }

        if (str_contains($serviceName, 'exhaust')) {
            self::addPartToDeduct($partsToDeduct, 'AK-EVO-911T', 1);
        }

        if (str_contains($serviceName, 'suspension') || str_contains($serviceName, 'shock')) {
            self::addPartToDeduct($partsToDeduct, 'OH-TTX-GP01', 1);
        }

        // 2. Resolve parts by matching selected services array names
        $selectedServices = $booking->selected_services ?? [];
        if (is_array($selectedServices)) {
            foreach ($selectedServices as $service) {
                $name = strtolower($service['name'] ?? '');
                if (str_contains($name, 'oil change')) {
                    self::addPartToDeduct($partsToDeduct, 'ENG-OIL-10W40', 2);
                    self::addPartToDeduct($partsToDeduct, 'ENG-FLT-HF138', 1);
                }
                if (str_contains($name, 'brake')) {
                    self::addPartToDeduct($partsToDeduct, 'BR-PAD-CC01', 1);
                }
                if (str_contains($name, 'exhaust')) {
                    self::addPartToDeduct($partsToDeduct, 'AK-EVO-911T', 1);
                }
                if (str_contains($name, 'suspension') || str_contains($name, 'shock')) {
                    self::addPartToDeduct($partsToDeduct, 'OH-TTX-GP01', 1);
                }
            }
        }

        // 3. Resolve parts from booking notes/remarks.
        // Mechanics can specify "Parts: SKU-ID xQty" (e.g. "BR-GT6-2024 x2") in booking notes.
        if (! empty($booking->notes)) {
            if (preg_match_all('/([A-Z0-9\-]{4,25})\s*x\s*(\d+)/i', $booking->notes, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $sku = strtoupper($match[1]);
                    $qty = (int) $match[2];
                    self::addPartToDeduct($partsToDeduct, $sku, $qty);
                }
            }
        }

        // 4. Perform deductions and log movements
        foreach ($partsToDeduct as $sku => $data) {
            $part = $data['part'];
            $qty = $data['qty'];

            if ($qty <= 0) {
                continue;
            }

            $prev = $part->stock_qty;
            $new = max(0, $prev - $qty);

            $part->forceFill(['stock_qty' => $new])->save();

            StockMovement::create([
                'part_id' => $part->id,
                'movement_type' => 'Service Usage',
                'quantity' => -$qty,
                'previous_stock' => $prev,
                'new_stock' => $new,
                'remarks' => "Auto-deducted for Booking #{$booking->id} (".substr($booking->service_name, 0, 80).')',
                'created_by' => $booking->mechanic_id ?? auth()->id(),
            ]);

            Log::info("InventoryIntegrator: Auto-deducted {$qty} units of part {$sku}. Stock: {$prev} -> {$new}");
        }
    }

    private static function addPartToDeduct(array &$partsToDeduct, string $sku, int $qty): void
    {
        $sku = strtoupper($sku);
        if (isset($partsToDeduct[$sku])) {
            $partsToDeduct[$sku]['qty'] += $qty;

            return;
        }

        $part = InventoryPart::where('sku', $sku)->first();
        if ($part) {
            $partsToDeduct[$sku] = [
                'part' => $part,
                'qty' => $qty,
            ];
        }
    }
}
