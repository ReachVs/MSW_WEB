<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'service_name' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'selected_services' => ['nullable', 'array'],
            'selected_services.*.id' => ['required_with:selected_services', 'integer', 'exists:services,id'],
            'selected_services.*.name' => ['nullable', 'string', 'max:255'],
            'selected_services.*.price' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'bike_name' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:255'],
            'engine_capacity' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'string', 'in:'.implode(',', Booking::statuses())],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $selectedServices = collect($this->input('selected_services', []))
                ->pluck('id')
                ->filter();

            if ($selectedServices->isEmpty() && ! $this->filled('service_id')) {
                $validator->errors()->add(
                    'selected_services',
                    'Please select at least one valid service.'
                );
            }
        });
    }
}
