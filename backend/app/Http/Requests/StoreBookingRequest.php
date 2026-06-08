<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_name' => ['required', 'string', 'max:255'],
            'service_id' => ['nullable', 'integer'],
            'selected_services' => ['nullable', 'array'],
            'selected_services.*.id' => ['nullable', 'integer'],
            'selected_services.*.name' => ['required_with:selected_services', 'string', 'max:255'],
            'selected_services.*.price' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'bike_name' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255'],
            'engine_capacity' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'string', 'in:' . implode(',', Booking::statuses())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
