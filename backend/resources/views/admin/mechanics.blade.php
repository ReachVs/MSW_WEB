@extends('layouts.admin')
@section('title', 'Mechanics')
@push('head')
<style>
    .mechanic-cards-grid {
        display: grid;
        gap: 24px;
        align-items: stretch;
        grid-template-columns: minmax(0, 1fr);
        width: 100%;
    }

    .mechanic-card {
        min-width: 0;
        height: 100%;
    }

    .mechanic-cards-grid.portal-grid {
        max-width: 1520px;
        margin-inline: auto;
    }

    @media (min-width: 760px) {
        .mechanic-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1220px) {
        .mechanic-cards-grid.portal-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1880px) {
        .mechanic-cards-grid.portal-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
</style>
@endpush
@php
    $page = 'mechanics';
    $canCreateMechanic = $canCreateMechanic ?? false;
    $canEditMechanic = $canEditMechanic ?? false;
    $mechanicLayoutClass = $canCreateMechanic
        ? 'grid items-start gap-gutter 2xl:grid-cols-[22rem_minmax(0,1fr)]'
        : 'block';
    $mechanicCardsWrapperClass = $canCreateMechanic || $canEditMechanic
        ? 'mechanic-cards-grid admin-grid'
        : 'mechanic-cards-grid portal-grid';
    $statusBadgeClasses = [
        'available' => 'bg-[#10B981] text-white border-[#10B981]',
        'busy' => 'bg-[#F59E0B] text-white border-[#F59E0B]',
        'off' => 'bg-[#6B7280] text-white border-[#6B7280]',
    ];
@endphp

@section('content')
<section class="space-y-lg">
    <div class="border border-outline-variant bg-surface-container-lowest p-lg">
        <div class="flex flex-col gap-md lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-label-sm uppercase tracking-widest text-outline">Workshop Specialists</p>
                <h1 class="mt-xs font-headline-lg text-primary uppercase tracking-tighter">Mechanic Management</h1>
                <p class="mt-sm max-w-3xl text-sm text-on-surface-variant">
                    View every specialist, track live workload, and keep mechanic assignments synced with queue, calendar, dashboard, and customer bookings.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-sm md:grid-cols-4">
                <div class="border border-primary bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Total</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['total'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Available</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['available'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Busy</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['busy'] }}</p>
                </div>
                <div class="border border-outline-variant bg-white px-md py-sm">
                    <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Off Duty</p>
                    <p class="mt-xs text-2xl font-bold text-primary">{{ $statistics['off'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ $mechanicLayoutClass }}">
        @if($canCreateMechanic)
            <aside class="space-y-lg 2xl:sticky 2xl:top-lg">
                <div class="border border-outline-variant bg-surface-container-lowest p-md">
                    <p class="font-label-sm uppercase tracking-widest text-outline">Add New Mechanic</p>
                    <form method="POST" action="{{ route('admin.mechanics.store') }}" class="mt-md space-y-sm">
                        @csrf
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Specialization</label>
                            <input type="text" name="specialization" value="{{ old('specialization') }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" placeholder="ECU Tuning, Brake Systems, QA, etc.">
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Status</label>
                            <select name="status" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                                @foreach($mechanicStatuses as $mechanicStatus)
                                    <option value="{{ $mechanicStatus }}" @selected(old('status', 'available') === $mechanicStatus)>
                                        {{ ucfirst($mechanicStatus) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="w-full bg-primary px-md py-sm font-label-sm uppercase tracking-widest text-on-primary hover:bg-secondary">
                            Save Mechanic
                        </button>
                    </form>
                </div>

                <div class="border border-outline-variant bg-surface-container-lowest p-md">
                    <p class="font-label-sm uppercase tracking-widest text-outline">Shared Standard</p>
                    <div class="mt-sm space-y-sm text-sm text-on-surface-variant">
                        <p>1. Add mechanics once in admin.</p>
                        <p>2. Assign mechanics directly on queue, dashboard, calendar, or walk-in creation.</p>
                        <p>3. The same assignment flows into customer active cards, archive history, and the mechanic portal.</p>
                    </div>
                </div>
            </aside>
        @endif

        <div class="{{ $mechanicCardsWrapperClass }}">
            @forelse($mechanics as $mechanic)
                <div class="mechanic-card flex flex-col border border-outline-variant bg-surface-container-lowest p-md">
                    <div class="flex items-start justify-between gap-md">
                        <div class="min-w-0">
                            <p class="font-headline-md text-primary uppercase">{{ $mechanic->name }}</p>
                            <p class="mt-xs text-sm text-on-surface-variant">
                                {{ $mechanic->specialization ?: 'General workshop specialist' }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusBadgeClasses[$mechanic->status->value] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                            {{ ucfirst($mechanic->status->value) }}
                        </span>
                    </div>

                    <div class="mt-md grid grid-cols-2 gap-sm">
                        <div class="rounded border border-outline-variant bg-white px-sm py-sm">
                            <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Active Jobs</p>
                            <p class="mt-xs text-2xl font-bold text-primary">{{ $mechanic->active_jobs_count }}</p>
                        </div>
                        <div class="rounded border border-outline-variant bg-white px-sm py-sm">
                            <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Completed</p>
                            <p class="mt-xs text-2xl font-bold text-primary">{{ $mechanic->completed_jobs_count }}</p>
                        </div>
                    </div>

                    <div class="mt-md rounded border border-outline-variant bg-white p-sm">
                        <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Current Assignments</p>
                        <div class="mt-sm space-y-sm text-sm text-on-surface-variant">
                            @forelse($mechanic->bookings as $booking)
                                <div class="rounded border border-outline-variant bg-surface-container-low p-sm">
                                    <div class="flex flex-wrap items-start justify-between gap-sm">
                                        <div class="min-w-0">
                                            <p class="font-bold text-primary uppercase wrap-break-word">
                                                {{ trim(($booking->bike_name ?? '') . ' ' . ($booking->model ?? '')) ?: $booking->service_name }}
                                            </p>
                                            <p class="mt-xs text-[11px] uppercase tracking-widest text-on-surface-variant wrap-break-word">
                                                {{ $booking->service_name }}
                                            </p>
                                        </div>
                                        <span class="text-[10px] font-label-sm uppercase tracking-widest text-outline">
                                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                                        </span>
                                    </div>
                                    <p class="mt-sm text-[11px] uppercase tracking-widest text-on-surface-variant">
                                        {{ $booking->customer_name }}
                                    </p>
                                    <p class="mt-xs text-sm text-on-surface-variant">
                                        {{ $booking->starts_at?->format('d M Y • h:i A') }}
                                    </p>
                                </div>
                            @empty
                                <p class="rounded border border-dashed border-outline-variant bg-surface-container-low p-sm">
                                    No active bookings assigned.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    @if($canEditMechanic)
                        <details class="mt-md rounded border border-outline-variant bg-white">
                            <summary class="cursor-pointer list-none px-sm py-sm font-label-sm uppercase tracking-widest text-primary hover:bg-surface-container-high">
                                Edit Mechanic
                            </summary>
                            <form method="POST" action="{{ route('admin.mechanics.update', $mechanic) }}" class="space-y-sm border-t border-outline-variant p-sm">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label class="block font-label-sm uppercase tracking-widest text-outline">Name</label>
                                    <input type="text" name="name" value="{{ old('name.'.$mechanic->id, $mechanic->name) }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                                </div>
                                <div>
                                    <label class="block font-label-sm uppercase tracking-widest text-outline">Specialization</label>
                                    <input type="text" name="specialization" value="{{ old('specialization.'.$mechanic->id, $mechanic->specialization) }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                                </div>
                                <div>
                                    <label class="block font-label-sm uppercase tracking-widest text-outline">Status</label>
                                    <select name="status" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm">
                                        @foreach($mechanicStatuses as $mechanicStatus)
                                            <option value="{{ $mechanicStatus }}" @selected($mechanic->status->value === $mechanicStatus)>
                                                {{ ucfirst($mechanicStatus) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="w-full border border-outline-variant bg-surface-container-high px-md py-sm font-label-sm uppercase tracking-widest text-primary hover:bg-primary hover:text-on-primary">
                                    Update Mechanic
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.mechanics.destroy', $mechanic) }}" class="mt-xs p-sm pt-0" onsubmit="return confirm('Are you sure you want to delete this mechanic? This will unassign them from all their bookings.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full border border-error bg-white px-md py-sm font-label-sm uppercase tracking-widest text-error hover:bg-error hover:text-white transition-colors">
                                    Delete Mechanic
                                </button>
                            </form>
                        </details>
                    @endif
                </div>
            @empty
                <div class="basis-full border border-dashed border-outline-variant bg-white p-lg text-center text-on-surface-variant">
                    No mechanics found yet.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
