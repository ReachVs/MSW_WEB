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
            'title' => $this->service_name,
            'service_name' => $this->service_name,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'reminder_sent_at' => $this->reminder_sent_at?->toISOString(),
            'created_by' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
