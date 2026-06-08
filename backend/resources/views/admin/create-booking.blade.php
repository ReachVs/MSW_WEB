@extends('layouts.admin')
@section('title', 'New Walk-In Entry')
@php
    $page = 'queue';
@endphp

@section('content')
<section class="space-y-lg">
    <div class="border border-outline-variant bg-surface-container-lowest p-lg">
        <p class="font-label-sm uppercase tracking-widest text-outline">Walk-In Booking Flow</p>
        <h1 class="mt-xs font-headline-lg text-primary uppercase tracking-tighter">Add New Entry</h1>
        <p class="mt-sm max-w-3xl text-sm text-on-surface-variant">
            Use this page for customers who walk into the workshop instead of booking online.
            Fill in the customer, motorcycle, service, and schedule details, then save the booking directly into the queue.
        </p>
    </div>

    <div class="grid gap-gutter xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="border border-outline-variant bg-surface-container-lowest p-lg">
            <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-lg">
                @csrf

                <div class="grid gap-gutter md:grid-cols-2">
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Customer Information</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Name</label>
                            <input
                                type="text"
                                name="customer_name"
                                value="{{ old('customer_name') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Email</label>
                            <input
                                type="email"
                                name="customer_email"
                                value="{{ old('customer_email') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                placeholder="Optional for walk-in"
                            >
                        </div>
                    </div>

                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Motorcycle Information</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Bike Brand</label>
                            <input
                                type="text"
                                name="bike_name"
                                value="{{ old('bike_name') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Model</label>
                            <input
                                type="text"
                                name="model"
                                value="{{ old('model') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Plate Number</label>
                            <input
                                type="text"
                                name="plate_number"
                                value="{{ old('plate_number') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Engine Capacity</label>
                            <input
                                type="text"
                                name="engine_capacity"
                                value="{{ old('engine_capacity') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                placeholder="e.g. 1000"
                            >
                        </div>
                    </div>
                </div>

                <div class="grid gap-gutter md:grid-cols-2">
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Service & Status</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Services</label>
                            <p class="mt-xs text-xs text-on-surface-variant">Select one or multiple services for the walk-in customer.</p>
                            <div class="mt-sm max-h-72 space-y-xs overflow-y-auto border border-outline-variant bg-white p-sm">
                                @foreach($services as $service)
                                    <label class="flex items-start gap-sm border border-outline-variant bg-surface-container-low p-sm">
                                        <input
                                            type="checkbox"
                                            name="service_ids[]"
                                            value="{{ $service->id }}"
                                            @checked(in_array($service->id, old('service_ids', [])))
                                            class="mt-0.5"
                                        >
                                        <span class="flex-1">
                                            <span class="block text-sm font-bold text-primary">{{ $service->name }}</span>
                                            <span class="block text-xs uppercase tracking-widest text-outline">
                                                {{ $service->main_category ?: 'Workshop Service' }}
                                                @if($service->sub_category)
                                                    • {{ $service->sub_category }}
                                                @endif
                                            </span>
                                        </span>
                                        <span class="text-sm font-bold text-secondary">${{ number_format((float) $service->price, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Starting Status</label>
                            <select
                                name="status"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                                <option value="pending" @selected(old('status', 'confirmed') === 'pending')>Pending</option>
                                <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>Confirmed</option>
                                <option value="repair" @selected(old('status') === 'repair')>Repair</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Schedule</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Booking Date</label>
                            <input
                                type="date"
                                name="booking_date"
                                value="{{ old('booking_date', $defaultDate->toDateString()) }}"
                                min="{{ now()->toDateString() }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Time Slot</label>
                            <select
                                name="booking_time"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                required
                            >
                                @foreach($timeSlots as $slot)
                                    <option value="{{ $slot['value'] }}" @selected(old('booking_time') === $slot['value'])>
                                        {{ $slot['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Description / Notes</label>
                    <textarea
                        name="notes"
                        rows="5"
                        class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                        placeholder="Describe the customer request, issue, or workshop notes."
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-sm">
                    <button class="bg-primary px-lg py-sm font-label-sm uppercase tracking-widest text-on-primary hover:bg-secondary">
                        Save Walk-In Entry
                    </button>
                    <a href="{{ route('admin.queue') }}"
                       class="border border-outline-variant bg-white px-lg py-sm font-label-sm uppercase tracking-widest text-primary hover:bg-surface-container-high">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <aside class="space-y-lg">
            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <h2 class="font-headline-md text-primary uppercase">How It Works</h2>
                <div class="mt-md space-y-sm text-sm text-on-surface-variant">
                    <p>1. Enter walk-in customer information.</p>
                    <p>2. Assign the motorcycle details manually.</p>
                    <p>3. Pick the requested service from your workshop catalog.</p>
                    <p>4. Choose date, time, and starting status, then save.</p>
                </div>
            </div>

            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <h2 class="font-headline-md text-primary uppercase">Schedule Note</h2>
                <p class="mt-sm text-sm text-on-surface-variant">
                    If the selected time slot is already full, the system will stop the save and ask you to choose a different slot.
                </p>
            </div>
        </aside>
    </div>
</section>
@endsection
