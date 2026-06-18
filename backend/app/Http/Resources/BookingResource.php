<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->id,
            'title' => $this->service_name,
            'service_name' => $this->service_name,
            'service_id' => $this->service_id,
            'selected_services' => $this->selected_services ?? [],
            'category_key' => $this->getCategoryKey(),
            'main_category_label' => $this->getCategoryDetails()['label'] ?? null,
            'main_category_icon' => $this->getCategoryDetails()['icon'] ?? null,
            'image_url' => $this->getCategoryDetails()['image'] ?? '/mechanic-chain.png',
            'total_amount' => $this->total_amount !== null ? (float) $this->total_amount : null,
            'bike_name' => $this->bike_name,
            'model' => $this->model,
            'plate_number' => $this->plate_number,
            'engine_capacity' => $this->engine_capacity,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'booking_date' => $this->starts_at?->toDateString(),
            'booking_time' => $this->starts_at?->format('H:i'),
            'booking_time_label' => $this->starts_at?->format('h:i A'),
            'status' => $this->status,
            'current_status' => $this->status,
            'can_customer_cancel' => $this->canBeCancelledByCustomer(),
            'allowed_next_statuses' => $this->allowedNextStatuses(),
            'notes' => $this->notes,
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'mechanic' => $this->whenLoaded('mechanic', fn () => [
                'id' => $this->mechanic?->id,
                'name' => $this->mechanic?->name,
            ]),
            'created_by' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
