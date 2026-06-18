@extends('layouts.admin')
@section('title', 'Queue')
@php
    $page = 'queue';
    $statusRouteName = $statusRouteName ?? 'admin.bookings.status';
    $mechanicRouteName = $mechanicRouteName ?? 'admin.bookings.mechanic';
    $deleteRouteName = $deleteRouteName ?? 'admin.bookings.destroy';
    $createBookingRouteName = $createBookingRouteName ?? 'admin.bookings.create';
    $queueSyncRouteName = $queueSyncRouteName ?? 'admin.queue.sync';
    $canAssignMechanic = $canAssignMechanic ?? true;
    $canUpdateStatus = $canUpdateStatus ?? true;
    $canDeleteArchive = $canDeleteArchive ?? true;
    $showAddEntry = $showAddEntry ?? true;
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
    $categoryColors = [
        'washing' => 'border-green-600/30 bg-green-600/10 text-green-600',
        'tuning' => 'border-yellow-600/30 bg-yellow-600/10 text-yellow-600',
        'engine_checkup' => 'border-red-600/30 bg-red-600/10 text-red-600',
        'maintenance' => 'border-blue-600/30 bg-blue-600/10 text-blue-600',
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
    @if($showAddEntry)
        <div class="flex items-center justify-end">
            <a href="{{ route($createBookingRouteName) }}"
               class="bg-secondary text-on-secondary px-lg py-sm font-label-sm uppercase tracking-widest hover:bg-primary transition-colors duration-200">
                Add New Entry
            </a>
        </div>
    @endif
</section>

{{-- ACTIVE SEQUENCE --}}
<div class="grid grid-cols-12 gap-gutter">
    <div class="col-span-12 space-y-md">
        @if($activeJobs->isNotEmpty())
            <div class="mb-sm flex items-center gap-sm bg-[#F59E0B] px-md py-sm text-white">
                <span class="material-symbols-outlined text-[16px]">pending_actions</span>
                <h3 class="font-label-sm text-label-sm uppercase tracking-widest">
                    Active Sequence
                </h3>
            </div>

            <div class="grid gap-md lg:grid-cols-2">
                @foreach($activeJobs as $activeJob)
                    <div class="relative overflow-hidden border border-primary bg-surface-container-lowest p-md transition-colors hover:bg-white">
                        <div class="grid gap-md lg:grid-cols-[minmax(0,1fr)_22rem]">
                            <div class="space-y-xs">
                                <div class="flex flex-wrap items-center gap-sm">
                                    <span class="font-label-sm text-label-sm text-secondary uppercase">
                                        Priority {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $activeJob->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                    @if($activeJob->getCategoryKey())
                                        @php
                                            $catDetails = $activeJob->getCategoryDetails();
                                            $colorClass = $categoryColors[$activeJob->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    @endif
                                    <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest {{ $statusTagClasses[$activeJob->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                        {{ $statusLabels[$activeJob->status] ?? ucfirst(str_replace('_', ' ', $activeJob->status)) }}
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
                                @include('admin.partials.mechanic-assignment', [
                                    'booking' => $activeJob,
                                    'mechanics' => $mechanics,
                                    'mechanicRouteName' => $mechanicRouteName,
                                    'canAssignMechanic' => $canAssignMechanic,
                                ])
                                @if($canUpdateStatus && count($activeJob->allowedNextStatuses()) > 0)
                                    <form method="POST" action="{{ route($statusRouteName, $activeJob) }}" class="mt-sm flex gap-sm">
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
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- QUEUED LIST --}}
        @if($pendingJobs->isNotEmpty())
            <div class="border border-outline-variant">
                <div class="bg-[#6B7280] px-md py-sm border-b border-[#6B7280] font-label-sm uppercase tracking-widest text-white">
                    Pending Workflow
                </div>
                <div class="divide-y divide-outline-variant">
                    @foreach($pendingJobs as $item)
                        <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                            <div>
                                <div class="mb-xs flex flex-wrap items-center gap-sm">
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                    @if($item->getCategoryKey())
                                        @php
                                            $catDetails = $item->getCategoryDetails();
                                            $colorClass = $categoryColors[$item->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    @endif
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
                                <div class="w-full space-y-sm">
                                    @include('admin.partials.mechanic-assignment', [
                                        'booking' => $item,
                                        'mechanics' => $mechanics,
                                        'mechanicRouteName' => $mechanicRouteName,
                                        'canAssignMechanic' => $canAssignMechanic,
                                    ])
                                    @if($canUpdateStatus && count($item->allowedNextStatuses()) > 0)
                                        <form method="POST" action="{{ route($statusRouteName, $item) }}" class="flex gap-sm">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($confirmedJobs->isNotEmpty())
            <div class="border border-outline-variant">
                <div class="bg-[#2563EB] px-md py-sm border-b border-[#2563EB] font-label-sm uppercase tracking-widest text-white">
                    Confirmed Workflow
                </div>
                <div class="divide-y divide-outline-variant">
                    @foreach($confirmedJobs as $item)
                        <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                            <div>
                                <div class="mb-xs flex flex-wrap items-center gap-sm">
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                    @if($item->getCategoryKey())
                                        @php
                                            $catDetails = $item->getCategoryDetails();
                                            $colorClass = $categoryColors[$item->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    @endif
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
                                <div class="w-full space-y-sm">
                                    @include('admin.partials.mechanic-assignment', [
                                        'booking' => $item,
                                        'mechanics' => $mechanics,
                                        'mechanicRouteName' => $mechanicRouteName,
                                        'canAssignMechanic' => $canAssignMechanic,
                                    ])
                                    @if($canUpdateStatus && count($item->allowedNextStatuses()) > 0)
                                        <form method="POST" action="{{ route($statusRouteName, $item) }}" class="flex gap-sm">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($waitingPartJobs->isNotEmpty())
            <div class="border border-outline-variant">
                <div class="bg-[#8B5CF6] px-md py-sm border-b border-[#8B5CF6] font-label-sm uppercase tracking-widest text-white">
                    Waiting Part Workflow
                </div>
                <div class="divide-y divide-outline-variant">
                    @foreach($waitingPartJobs as $item)
                        <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                            <div>
                                <div class="mb-xs flex flex-wrap items-center gap-sm">
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                    @if($item->getCategoryKey())
                                        @php
                                            $catDetails = $item->getCategoryDetails();
                                            $colorClass = $categoryColors[$item->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    @endif
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
                                <div class="w-full space-y-sm">
                                    @include('admin.partials.mechanic-assignment', [
                                        'booking' => $item,
                                        'mechanics' => $mechanics,
                                        'mechanicRouteName' => $mechanicRouteName,
                                        'canAssignMechanic' => $canAssignMechanic,
                                    ])
                                    @if($canUpdateStatus && count($item->allowedNextStatuses()) > 0)
                                        <form method="POST" action="{{ route($statusRouteName, $item) }}" class="flex gap-sm">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($readyPickupJobs->isNotEmpty())
            <div class="border border-outline-variant">
                <div class="bg-[#10B981] px-md py-sm border-b border-[#10B981] font-label-sm uppercase tracking-widest text-white">
                    Ready Pickup Workflow
                </div>
                <div class="divide-y divide-outline-variant">
                    @foreach($readyPickupJobs as $item)
                        <div class="p-md grid gap-md hover:bg-surface-container-lowest transition-colors lg:grid-cols-[minmax(0,1fr)_24rem]">
                            <div>
                                <div class="mb-xs flex flex-wrap items-center gap-sm">
                                    <span class="rounded border border-outline-variant bg-white px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                        {{ $item->user_id ? 'Booking' : 'Walk In' }}
                                    </span>
                                    @if($item->getCategoryKey())
                                        @php
                                            $catDetails = $item->getCategoryDetails();
                                            $colorClass = $categoryColors[$item->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                        @endphp
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-sm">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    @endif
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
                                <div class="w-full space-y-sm">
                                    @include('admin.partials.mechanic-assignment', [
                                        'booking' => $item,
                                        'mechanics' => $mechanics,
                                        'mechanicRouteName' => $mechanicRouteName,
                                        'canAssignMechanic' => $canAssignMechanic,
                                    ])
                                    @if($canUpdateStatus && count($item->allowedNextStatuses()) > 0)
                                        <form method="POST" action="{{ route($statusRouteName, $item) }}" class="flex gap-sm">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($activeJobs->isEmpty() && $pendingJobs->isEmpty() && $confirmedJobs->isEmpty() && $waitingPartJobs->isEmpty() && $readyPickupJobs->isEmpty() && $archive->isEmpty())
            <div class="border border-outline-variant p-md text-on-surface-variant text-center">
                No active or archived jobs in the queue.
            </div>
        @endif
    </div>

    {{-- SERVICE ARCHIVE --}}
    @if($archive->isNotEmpty())
        <div class="col-span-12 border border-outline-variant bg-white">
            <div class="bg-surface-container-low px-md py-sm border-b border-outline-variant font-label-sm uppercase tracking-widest text-outline">
                Service Archive
            </div>
            <div class="overflow-x-auto bg-white">
                <table class="w-full border-collapse bg-white text-left">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container-lowest">
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Ref ID</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Date</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Unit</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Description</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Service Type</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Mechanic</th>
                            <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline">Status</th>
                            @if($canDeleteArchive)
                                <th class="px-md py-sm font-label-sm text-label-sm uppercase text-outline"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant bg-white">
                        @foreach($archive as $record)
                        <tr class="bg-white transition-colors hover:bg-surface-container-low">
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
                            <td class="px-md py-md">
                                <div class="font-body-md font-medium text-primary">{{ $record->service_name }}</div>
                                @if($record->getCategoryKey())
                                    @php
                                        $catDetails = $record->getCategoryDetails();
                                        $colorClass = $categoryColors[$record->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                    @endphp
                                    <div class="mt-xs">
                                        <span class="rounded border px-sm py-xs text-[10px] font-label-sm uppercase tracking-widest inline-flex items-center gap-xs {{ $colorClass }}">
                                            <span class="material-symbols-outlined text-[14px]">{{ $catDetails['icon'] }}</span>
                                            {{ $catDetails['label'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-md py-md font-body-md text-on-surface-variant">{{ $record->mechanic->name ?? 'N/A' }}</td>
                            <td class="px-md py-md">
                                <span class="text-label-sm uppercase px-sm py-1 font-bold rounded-full border {{ $statusTagClasses[$record->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                    {{ $statusLabels[$record->status] ?? ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </td>
                            @if($canDeleteArchive)
                                <td class="px-md py-md text-right">
                                    <form method="POST" action="{{ route($deleteRouteName, $record) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-md py-1 border border-error text-error font-label-sm text-label-sm uppercase hover:bg-error hover:text-white transition-colors">Delete</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const queueSyncUrl = @json(route($queueSyncRouteName));
        const syncIntervalMs = 1500;
        const queueSyncChannelName = 'madape-queue-sync';
        const queueSyncStorageKey = 'madape-queue-sync';
        const pageInstanceId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
        let currentSignature = null;
        let isChecking = false;
        let hasPendingCrossTabRefresh = false;
        const broadcastChannel = typeof BroadcastChannel !== 'undefined'
            ? new BroadcastChannel(queueSyncChannelName)
            : null;

        const reloadQueue = () => {
            window.location.reload();
        };

        const notifyOtherQueueTabs = () => {
            const payload = {
                type: 'queue-updated',
                source: pageInstanceId,
                timestamp: Date.now(),
            };

            if (broadcastChannel) {
                broadcastChannel.postMessage(payload);
            }

            try {
                localStorage.setItem(queueSyncStorageKey, JSON.stringify(payload));
            } catch (error) {
                console.warn('Queue sync storage broadcast failed:', error);
            }
        };

        const handleIncomingQueueUpdate = (payload) => {
            if (!payload || payload.source === pageInstanceId || payload.type !== 'queue-updated') {
                return;
            }

            if (document.visibilityState === 'visible') {
                reloadQueue();
                return;
            }

            hasPendingCrossTabRefresh = true;
        };

        const hasActiveFormInteraction = () => {
            const activeElement = document.activeElement;

            if (!activeElement) {
                return false;
            }

            return ['INPUT', 'SELECT', 'TEXTAREA'].includes(activeElement.tagName);
        };

        const shouldRefreshQueue = () => {
            return document.visibilityState === 'visible' && !hasActiveFormInteraction();
        };

        const fetchSignature = async () => {
            const response = await fetch(queueSyncUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`Queue sync failed with status ${response.status}`);
            }

            const data = await response.json();

            return data.signature ?? null;
        };

        const checkForQueueUpdates = async () => {
            if (!shouldRefreshQueue() || isChecking) {
                return;
            }

            isChecking = true;

            try {
                const latestSignature = await fetchSignature();

                if (!latestSignature) {
                    return;
                }

                if (!currentSignature) {
                    currentSignature = latestSignature;
                    return;
                }

                if (latestSignature !== currentSignature) {
                    reloadQueue();
                    return;
                }

                currentSignature = latestSignature;
            } catch (error) {
                console.error('Queue sync check failed:', error);
            } finally {
                isChecking = false;
            }
        };

        document.querySelectorAll('form[action*="/bookings/"]').forEach((form) => {
            form.addEventListener('submit', notifyOtherQueueTabs);
        });

        if (broadcastChannel) {
            broadcastChannel.addEventListener('message', (event) => {
                handleIncomingQueueUpdate(event.data);
            });
        }

        window.addEventListener('storage', (event) => {
            if (event.key !== queueSyncStorageKey || !event.newValue) {
                return;
            }

            try {
                handleIncomingQueueUpdate(JSON.parse(event.newValue));
            } catch (error) {
                console.warn('Queue sync storage payload parsing failed:', error);
            }
        });

        checkForQueueUpdates();
        setInterval(checkForQueueUpdates, syncIntervalMs);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible' && hasPendingCrossTabRefresh) {
                hasPendingCrossTabRefresh = false;
                reloadQueue();
                return;
            }

            checkForQueueUpdates();
        });
    })();
</script>
@endpush
