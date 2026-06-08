<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class AdminStoreBookingRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'bike_name' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255'],
            'engine_capacity' => ['nullable', 'string', 'max:255'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'string', 'in:' . implode(',', [
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_REPAIR,
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
