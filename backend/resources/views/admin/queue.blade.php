@extends('layouts.admin')
@section('title', 'Queue')
@php
    $page = 'queue';
    $statusLabels = [
        \App\Models\Booking::STATUS_PENDING => 'Pending',
        \App\Models\Booking::STATUS_CONFIRMED => 'Confirmed',
        \App\Models\Booking::STATUS_REPAIR => 'Repair',
        \App\Models\Booking::STATUS_WAITING_PART => 'Waiting Part',
        \App\Models\Booking::STATUS_READY_PICKUP => 'Ready Pickup',
        \App\Models\Booking::STATUS_COMPLETED => 'Completed',
        \App\Models\Booking::STATUS_CANCELLED => 'Cancelled',
    ];
    $statusTagClasses = [
        \App\Models\Booking::STATUS_PENDING => 'bg-[#6B7280] text-white border-[#6B7280]',
        \App\Models\Booking::STATUS_CONFIRMED => 'bg-[#2563EB] text-white border-[#2563EB]',
        \App\Models\Booking::STATUS_REPAIR => 'bg-[#F59E0B] text-white border-[#F59E0B]',
        \App\Models\Booking::STATUS_WAITING_PART => 'bg-[#8B5CF6] text-white border-[#8B5CF6]',
        \App\Models\Booking::STATUS_READY_PICKUP => 'bg-[#10B981] text-white border-[#10B981]',
        \App\Models\Booking::STATUS_COMPLETED => 'bg-[#059669] text-white border-[#059669]',
        \App\Models\Booking::STATUS_CANCELLED => 'bg-[#EF4444] text-white border-[#EF4444]',
    ];
@endphp

@section('content')
{{-- LIVE QUEUE STATUS HEADER --}}
<section class="grid grid-cols-1 md:grid-cols-3 gap-md border-b border-outline-variant pb-lg">
    <div class="space-y-xs">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Workshop Overview</span>
        <h2 class="font-headline-lg text-headline-lg text-primary flex items-baseline gap-sm">
            Active Jobs <span class="text-secondary">({{ $pendingJobs->count() + $confirmedJobs->count() + $activeJobs->count() + $waitingPartJobs->count() + $readyPickupJobs->count() }})</span>
        </h2>
    </div>
    <div class="flex flex-col justify-end">
        <div class="flex items-center justify-between border-l border-outline-variant pl-md">
            <div>
                <p class="font-label-sm text-label-sm uppercase text-outline">Waiting in Queue</p>
                <p class="font-headline-md text-headline-md text-primary">{{ str_pad($pendingJobs->count() + $confirmedJobs->count() + $waitingPartJobs->count() + $readyPickupJobs->count(), 2, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div class="text-right">
                <p class="font-label-sm text-label-sm uppercase text-outline">Active Repair</p>
                <p class="font-headline-md text-headline-md text-primary">{{ str_pad($activeJobs->count(), 2, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>
    </div>
    <div class="flex items-center justify-end">
        <a href="{{ route('admin.bookings.create') }}"
           class="bg-secondary text-on-secondary px-lg py-sm font-label-sm uppercase tracking-widest hover:bg-primary transition-colors duration-200">
            Add New Entry
        </a>
    </div>
</section>

{{-- ACTIVE SEQUENCE --}}
<div class="grid grid-cols-12 gap-gutter">
    <div class="col-span-12 space-y-md">
        <div class="mb-sm flex items-center gap-sm bg-[#F59E0B] px-md py-sm text-white">
            <span class="material-symbols-outlined text-[16px]">pending_actions</span>
            <h3 class="font-label-sm text-label-sm uppercase tracking-widest">
                Active Sequence
            </h3>
        </div>

        @if($activeJobs->isNotEmpty())
            <div class="grid gap-md lg:grid-cols-2">
                @foreach($activeJobs as $activeJob)
                    <div class="border border-primary p-md relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-[#F59E0B] text-white px-md py-xs font-label-sm uppercase tracking-widest">Repair</div>
                        <div class="grid gap-md lg:grid-cols-[minmax(0,1fr)_22rem]">
                            <div class="space-y-xs">
                                <div class="flex flex-wrap items-center gap-sm">
                                    <span class="font-label-sm text-label-sm text-secondary uppercase">
                                        Priority {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $activeJob->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                </div>
                                <h4 class="font-headline-md text-headline-md text-primary">
                                    {{ trim(($activeJob->bike_name ?? '') . ' ' . ($activeJob->model ?? '')) ?: $activeJob->service_name }}
                                </h4>
                                <div class="flex gap-md text-on-surface-variant font-body-md">
                                    <span class="flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-sm">person</span>{{ $activeJob->mechanic->name ?? 'N/A' }}
                                    </span>
                                    <span class="flex items-center gap-xs">
                                        <span class="material-symbols-outlined text-sm">settings_suggest</span>{{ $activeJob->service_name }}
                                    </span>
                                </div>
                                <div class="grid gap-xs pt-sm text-[11px] uppercase tracking-widest text-on-surface-variant md:grid-cols-2">
                                    <div>Customer: <span class="font-bold text-primary">{{ $activeJob->customer_name ?? 'N/A' }}</span></div>
                                    <div>Plate: <span class="font-bold text-primary">{{ $activeJob->plate_number ?? 'N/A' }}</span></div>
                                    <div>Engine: <span class="font-bold text-primary">{{ $activeJob->engine_capacity ?? 'N/A' }}</span></div>
                                    <div>Booking Date: <span class="font-bold text-primary">{{ $activeJob->starts_at?->format('d M Y') ?? 'N/A' }}</span></div>
                                    <div>Time Slot: <span class="font-bold text-primary">{{ $activeJob->starts_at?->format('h:i A') ?? 'N/A' }}</span></div>
                                </div>
                                @if($activeJob->notes)
                                    <div class="border border-outline-variant bg-surface-container-lowest p-sm mt-sm">
                                        <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">Customer Description</p>
                                        <p class="mt-xs text-sm text-primary leading-relaxed">{{ $activeJob->notes }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <form method="POST" action="{{ route('admin.bookings.status', $activeJob) }}" class="mt-sm flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border border-outline-variant bg-white px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest">
                                        @foreach($activeJob->allowedNextStatuses() as $nextStatus)
                                            <option value="{{ $nextStatus }}">
                                                {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="border border-outline-variant px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest hover:bg-surface-container-high transition-colors">
                                        Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border border-outline-variant p-md text-on-surface-variant">
                No job currently in service.
            </div>
        @endif

        {{-- QUEUED LIST --}}
        <div class="border border-outline-variant">
            <div class="bg-[#6B7280] px-md py-sm border-b border-[#6B7280] font-label-sm uppercase tracking-widest text-white">
                Pending Workflow
            </div>
            <div class="divide-y divide-outline-variant">
                @forelse($pendingJobs as $item)
                    <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <div>
                            <div class="mb-xs flex flex-wrap items-center gap-sm">
                                <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                    {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                </span>
                                <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusTagClasses[$item->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </div>
                            <p class="font-body-md text-body-md font-medium uppercase">
                                {{ trim(($item->bike_name ?? '') . ' ' . ($item->model ?? '')) ?: $item->service_name }}
                            </p>
                            <p class="font-label-sm text-label-sm text-outline uppercase">{{ $item->mechanic->name ?? 'N/A' }} • {{ $item->service_name }}</p>
                            <div class="mt-sm grid gap-xs text-[11px] uppercase tracking-widest text-on-surface-variant md:grid-cols-2">
                                <div>Customer: <span class="font-bold text-primary">{{ $item->customer_name ?? 'N/A' }}</span></div>
                                <div>Plate: <span class="font-bold text-primary">{{ $item->plate_number ?? 'N/A' }}</span></div>
                                <div>Engine: <span class="font-bold text-primary">{{ $item->engine_capacity ?? 'N/A' }}</span></div>
                                <div>Booking Date: <span class="font-bold text-primary">{{ $item->starts_at?->format('d M Y') ?? 'N/A' }}</span></div>
                                <div>Time Slot: <span class="font-bold text-primary">{{ $item->starts_at?->format('h:i A') ?? 'N/A' }}</span></div>
                            </div>
                            @if($item->notes)
                                <div class="border border-outline-variant bg-surface-container-lowest p-sm mt-sm">
                                    <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">Customer Description</p>
                                    <p class="mt-xs text-sm text-primary leading-relaxed">{{ $item->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-start justify-end gap-lg">
                            @if(count($item->allowedNextStatuses()) > 0)
                                <form method="POST" action="{{ route('admin.bookings.status', $item) }}" class="flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border border-outline-variant bg-white px-sm py-1 font-label-sm text-label-sm uppercase">
                                        @foreach($item->allowedNextStatuses() as $nextStatus)
                                            <option value="{{ $nextStatus }}">
                                                {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="px-md py-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase hover:bg-secondary transition-colors">Update Status</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-md text-on-surface-variant">No jobs currently in the queue.</div>
                @endforelse
            </div>
        </div>

        <div class="border border-outline-variant">
            <div class="bg-[#2563EB] px-md py-sm border-b border-[#2563EB] font-label-sm uppercase tracking-widest text-white">
                Confirmed Workflow
            </div>
            <div class="divide-y divide-outline-variant">
                @forelse($confirmedJobs as $item)
                    <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <div>
                            <div class="mb-xs flex flex-wrap items-center gap-sm">
                                <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                    {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                </span>
                                <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusTagClasses[$item->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </div>
                            <p class="font-body-md text-body-md font-medium uppercase">
                                {{ trim(($item->bike_name ?? '') . ' ' . ($item->model ?? '')) ?: $item->service_name }}
                            </p>
                            <p class="font-label-sm text-label-sm text-outline uppercase">{{ $item->mechanic->name ?? 'N/A' }} • {{ $item->service_name }}</p>
                            <div class="mt-sm grid gap-xs text-[11px] uppercase tracking-widest text-on-surface-variant md:grid-cols-2">
                                <div>Customer: <span class="font-bold text-primary">{{ $item->customer_name ?? 'N/A' }}</span></div>
                                <div>Plate: <span class="font-bold text-primary">{{ $item->plate_number ?? 'N/A' }}</span></div>
                                <div>Engine: <span class="font-bold text-primary">{{ $item->engine_capacity ?? 'N/A' }}</span></div>
                                <div>Booking Date: <span class="font-bold text-primary">{{ $item->starts_at?->format('d M Y') ?? 'N/A' }}</span></div>
                                <div>Time Slot: <span class="font-bold text-primary">{{ $item->starts_at?->format('h:i A') ?? 'N/A' }}</span></div>
                            </div>
                            @if($item->notes)
                                <div class="border border-outline-variant bg-surface-container-lowest p-sm mt-sm">
                                    <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">Customer Description</p>
                                    <p class="mt-xs text-sm text-primary leading-relaxed">{{ $item->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-start justify-end gap-lg">
                            @if(count($item->allowedNextStatuses()) > 0)
                                <form method="POST" action="{{ route('admin.bookings.status', $item) }}" class="flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border border-outline-variant bg-white px-sm py-1 font-label-sm text-label-sm uppercase">
                                        @foreach($item->allowedNextStatuses() as $nextStatus)
                                            <option value="{{ $nextStatus }}">
                                                {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="px-md py-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase hover:bg-secondary transition-colors">Update Status</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-md text-on-surface-variant">No confirmed jobs scheduled right now.</div>
                @endforelse
            </div>
        </div>

        <div class="border border-outline-variant">
            <div class="bg-[#8B5CF6] px-md py-sm border-b border-[#8B5CF6] font-label-sm uppercase tracking-widest text-white">
                Waiting Part Workflow
            </div>
            <div class="divide-y divide-outline-variant">
                @forelse($waitingPartJobs as $item)
                    <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <div>
                            <div class="mb-xs flex flex-wrap items-center gap-sm">
                                <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                    {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                </span>
                                <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusTagClasses[$item->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </div>
                            <p class="font-body-md text-body-md font-medium uppercase">
                                {{ trim(($item->bike_name ?? '') . ' ' . ($item->model ?? '')) ?: $item->service_name }}
                            </p>
                            <p class="font-label-sm text-label-sm text-outline uppercase">{{ $item->mechanic->name ?? 'N/A' }} • {{ $item->service_name }}</p>
                            <div class="mt-sm grid gap-xs text-[11px] uppercase tracking-widest text-on-surface-variant md:grid-cols-2">
                                <div>Customer: <span class="font-bold text-primary">{{ $item->customer_name ?? 'N/A' }}</span></div>
                                <div>Plate: <span class="font-bold text-primary">{{ $item->plate_number ?? 'N/A' }}</span></div>
                                <div>Engine: <span class="font-bold text-primary">{{ $item->engine_capacity ?? 'N/A' }}</span></div>
                                <div>Booking Date: <span class="font-bold text-primary">{{ $item->starts_at?->format('d M Y') ?? 'N/A' }}</span></div>
                                <div>Time Slot: <span class="font-bold text-primary">{{ $item->starts_at?->format('h:i A') ?? 'N/A' }}</span></div>
                            </div>
                            @if($item->notes)
                                <div class="border border-outline-variant bg-surface-container-lowest p-sm mt-sm">
                                    <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">Customer Description</p>
                                    <p class="mt-xs text-sm text-primary leading-relaxed">{{ $item->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-start justify-end gap-lg">
                            @if(count($item->allowedNextStatuses()) > 0)
                                <form method="POST" action="{{ route('admin.bookings.status', $item) }}" class="flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border border-outline-variant bg-white px-sm py-1 font-label-sm text-label-sm uppercase">
                                        @foreach($item->allowedNextStatuses() as $nextStatus)
                                            <option value="{{ $nextStatus }}">
                                                {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="px-md py-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase hover:bg-secondary transition-colors">Update Status</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-md text-on-surface-variant">No jobs are currently waiting for parts.</div>
                @endforelse
            </div>
        </div>

        <div class="border border-outline-variant">
            <div class="bg-[#10B981] px-md py-sm border-b border-[#10B981] font-label-sm uppercase tracking-widest text-white">
                Ready Pickup Workflow
            </div>
            <div class="divide-y divide-outline-variant">
                @forelse($readyPickupJobs as $item)
                    <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                        <div>
                            <div class="mb-xs flex flex-wrap items-center gap-sm">
                                <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                    {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                </span>
                                <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusTagClasses[$item->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </div>
                            <p class="font-body-md text-body-md font-medium uppercase">
                                {{ trim(($item->bike_name ?? '') . ' ' . ($item->model ?? '')) ?: $item->service_name }}
                            </p>
                            <p class="font-label-sm text-label-sm text-outline uppercase">{{ $item->mechanic->name ?? 'N/A' }} • {{ $item->service_name }}</p>
                            <div class="mt-sm grid gap-xs text-[11px] uppercase tracking-widest text-on-surface-variant md:grid-cols-2">
                                <div>Customer: <span class="font-bold text-primary">{{ $item->customer_name ?? 'N/A' }}</span></div>
                                <div>Plate: <span class="font-bold text-primary">{{ $item->plate_number ?? 'N/A' }}</span></div>
                                <div>Engine: <span class="font-bold text-primary">{{ $item->engine_capacity ?? 'N/A' }}</span></div>
                                <div>Booking Date: <span class="font-bold text-primary">{{ $item->starts_at?->format('d M Y') ?? 'N/A' }}</span></div>
                                <div>Time Slot: <span class="font-bold text-primary">{{ $item->starts_at?->format('h:i A') ?? 'N/A' }}</span></div>
                            </div>
                            @if($item->notes)
                                <div class="border border-outline-variant bg-surface-container-lowest p-sm mt-sm">
                                    <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">Customer Description</p>
                                    <p class="mt-xs text-sm text-primary leading-relaxed">{{ $item->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-start justify-end gap-lg">
                            @if(count($item->allowedNextStatuses()) > 0)
                                <form method="POST" action="{{ route('admin.bookings.status', $item) }}" class="flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="border border-outline-variant bg-white px-sm py-1 font-label-sm text-label-sm uppercase">
                                        @foreach($item->allowedNextStatuses() as $nextStatus)
                                            <option value="{{ $nextStatus }}">
                                                {{ $statusLabels[$nextStatus] ?? ucfirst(str_replace('_', ' ', $nextStatus)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="px-md py-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase hover:bg-secondary transition-colors">Update Status</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-md text-on-surface-variant">No jobs are currently ready for pickup.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- SERVICE ARCHIVE --}}
    <div class="col-span-12 border border-outline-variant">
        <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant font-label-sm uppercase tracking-widest text-outline">
            Service Archive
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant bg-surface-container-lowest">
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Ref ID</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Date</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Unit</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Description</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Service Type</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Mechanic</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Status</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($archive as $record)
                    <tr class="hover:bg-surface-container-lowest transition-colors">
                        <td class="px-md py-md font-mono text-body-md text-primary">#MA-{{ str_pad($record->id, 3, '0', STR_PAD_LEFT) }}-{{ $record->created_at->format('y') }}</td>
                        <td class="px-md py-md font-body-md text-on-surface-variant">{{ optional($record->ends_at ?? $record->updated_at)->format('d M Y') ?? 'N/A' }}</td>
                        <td class="px-md py-md">
                            <div class="font-body-md font-medium text-primary">
                                {{ trim(($record->bike_name ?? '') . ' ' . ($record->model ?? '')) ?: $record->service_name }}
                            </div>
                            <div class="mt-xs text-[11px] uppercase tracking-widest text-on-surface-variant">
                                Plate: <span class="font-bold text-primary">{{ $record->plate_number ?? 'N/A' }}</span>
                            </div>
                            <div class="text-[11px] uppercase tracking-widest text-on-surface-variant">
                                Engine: <span class="font-bold text-primary">{{ $record->engine_capacity ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-md py-md font-body-md text-on-surface-variant">
                            {{ $record->notes ?: 'No customer description provided.' }}
                        </td>
                        <td class="px-md py-md font-body-md text-on-surface-variant">{{ $record->service_name }}</td>
                        <td class="px-md py-md font-body-md text-on-surface-variant">{{ $record->mechanic->name ?? 'N/A' }}</td>
                        <td class="px-md py-md">
                            <span class="text-label-sm uppercase px-sm py-1 font-bold rounded-full border {{ $statusTagClasses[$record->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                {{ $statusLabels[$record->status] ?? ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td class="px-md py-md text-right">
                            <form method="POST" action="{{ route('admin.bookings.destroy', $record) }}">
                                @csrf
                                @method('DELETE')
                                <button class="px-md py-1 border border-error text-error font-label-sm text-label-sm uppercase hover:bg-error hover:text-white transition-colors">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" class="px-md py-md text-on-surface-variant">No completed jobs in the archive.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Removed the problematic JavaScript that animated mock timers --}}
@endpush
