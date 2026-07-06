@extends('layouts.admin')
@section('title', 'Dashboard')
@php
    $page = 'dashboard';
    $statusRouteName = $statusRouteName ?? 'admin.bookings.status';
    $mechanicRouteName = $mechanicRouteName ?? 'admin.bookings.mechanic';
    $queueRouteName = $queueRouteName ?? 'admin.queue';
    $canAssignMechanic = $canAssignMechanic ?? true;
    $canUpdateStatus = $canUpdateStatus ?? true;
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
{{-- SECTION 1: OVERVIEW METRICS --}}
<section class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
    <div class="bg-surface-container-lowest border border-primary p-md flex flex-col justify-between h-40 hover:scale-[1.01] transition-transform">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Active Jobs</span>
        <div class="flex items-baseline gap-sm">
            <span class="font-display-xl text-display-xl text-primary">{{ str_pad($activeJobs, 2, '0', STR_PAD_LEFT) }}</span>
            {{-- <span class="font-label-sm text-label-sm text-secondary">+2</span> --}}
        </div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant p-md flex flex-col justify-between h-40 hover:scale-[1.01] transition-transform">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Machines In Service</span>
        <div class="flex items-baseline gap-sm">
            <span class="font-display-xl text-display-xl text-primary">{{ str_pad($workshopCards->count(), 2, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>
    <div class="bg-surface-container-lowest border border-outline-variant p-md flex flex-col justify-between h-40 hover:scale-[1.01] transition-transform">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Completed Today</span>
        <div class="flex items-baseline gap-sm">
            <span class="font-display-xl text-display-xl text-primary">{{ str_pad($completedToday, 2, '0', STR_PAD_LEFT) }}</span>
            {{-- <span class="font-label-sm text-label-sm text-outline">Target 06</span> --}}
        </div>
    </div>
</section>

{{-- SECTION 2: WORKSHOP FEED --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
    <section class="lg:col-span-2 space-y-md">
        <div class="flex justify-between items-end">
            <h2 class="font-headline-md text-headline-md text-primary tracking-tighter uppercase">Workshop Floor</h2>
            <span class="font-label-sm text-label-sm text-outline uppercase tracking-widest">Live Updates</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            @forelse($workshopCards as $booking)
                <div class="bg-surface-container-lowest border border-outline-variant group hover:border-primary transition-all bg-surface-container-low">
                    <div class="p-md space-y-sm">
                        <div class="mb-xs flex flex-wrap items-center gap-sm">
                            <span class="rounded border border-outline-variant bg-white px-sm py-0.5 text-[10px] font-label-sm uppercase tracking-widest text-primary">
                                {{ $booking->user_id ? 'Booking' : 'Walk In' }}
                            </span>
                            @if($booking->getCategoryKey())
                                @php
                                    $catDetails = $booking->getCategoryDetails();
                                    $colorClass = $categoryColors[$booking->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                                @endphp
                                @if($catDetails)
                                <span class="rounded border px-sm py-0.5 text-[10px] font-label-sm uppercase tracking-widest inline-flex items-center gap-xs {{ $colorClass }}">
                                    <span class="material-symbols-outlined text-[12px]">{{ $catDetails['icon'] }}</span>
                                    {{ $catDetails['label'] }}
                                </span>
                                @endif
                            @endif
                            <span class="ml-auto font-label-sm text-[10px] uppercase tracking-widest px-2 py-0.5 border rounded {{ $statusTagClasses[$booking->status] ?? 'bg-surface-container-high text-primary border-outline-variant' }}">
                                {{ $statusLabels[$booking->status] ?? ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </div>
                        <div class="inline-block bg-secondary text-on-secondary px-sm py-xs font-label-sm text-label-sm tracking-widest uppercase mb-sm">
                            {{ $booking->service_name }}
                        </div>
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-headline-sm text-xl font-bold uppercase text-primary">
                                    {{ trim(($booking->bike_name ?? '') . ' ' . ($booking->model ?? '')) ?: 'Workshop Entry' }}
                                </h3>
                                <p class="font-label-sm text-label-sm text-outline uppercase">Mechanic: {{ $booking->mechanic->name ?? 'N/A' }}</p>
                                <p class="mt-xs font-label-sm text-[10px] uppercase tracking-widest text-outline">
                                    {{ $booking->customer_name ?? 'Walk-In Customer' }}
                                    @if($booking->customer_phone) • {{ $booking->customer_phone }} @endif
                                    • {{ $booking->customer_email }}
                                </p>
                                <p class="font-label-sm text-[10px] uppercase tracking-widest text-outline">
                                    {{ $booking->starts_at->format('d M Y • h:i A') }}
                                </p>
                            </div>
                            <span class="material-symbols-outlined text-outline">
                                @if($booking->status === \App\Models\Booking::STATUS_REPAIR) precision_manufacturing @else assignment_turned_in @endif
                            </span>
                        </div>
                        <div class="rounded border border-outline-variant bg-white p-sm">
                            <p class="text-[10px] font-label-sm uppercase tracking-widest text-outline">Service Summary</p>
                            <p class="mt-xs text-sm text-on-surface-variant">{{ $booking->service_name }}</p>
                        </div>
                        <div class="mt-md grid grid-cols-1 gap-sm">
                            @include('admin.partials.mechanic-assignment', [
                                'booking' => $booking,
                                'mechanics' => $mechanics,
                                'mechanicRouteName' => $mechanicRouteName,
                                'canAssignMechanic' => $canAssignMechanic,
                            ])
                            @if($canUpdateStatus && count($booking->allowedNextStatuses()) > 0)
                                <form method="POST" action="{{ route($statusRouteName, $booking) }}" class="flex gap-sm">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="flex-1 border border-outline-variant bg-white px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest">
                                        @foreach($booking->allowedNextStatuses() as $nextStatus)
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
                            <details class="border border-outline-variant bg-white">
                                <summary class="cursor-pointer list-none px-sm py-xs text-center font-label-sm text-[10px] uppercase tracking-widest text-primary hover:bg-surface-container-high">
                                    View Description
                                </summary>
                                <div class="border-t border-outline-variant p-sm text-sm text-on-surface-variant">
                                    {{ $booking->notes ?: 'No customer description submitted.' }}
                                </div>
                            </details>
                            <a href="{{ route($queueRouteName) }}" class="border border-outline-variant py-xs text-center font-label-sm text-[10px] uppercase tracking-widest hover:bg-surface-container-high transition-colors">
                                Open Queue
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-on-surface-variant col-span-2">No jobs currently in the workshop.</p>
            @endforelse
        </div>
    </section>

    <section class="space-y-md">
        {{-- System Alerts placeholder --}}
        <div class="flex justify-between items-end">
            <h2 class="font-headline-md text-headline-md text-primary tracking-tighter uppercase" style="font-size: 20px;">System Alerts</h2>
        </div>
        <div class="space-y-sm">
            <div class="border border-outline-variant p-md bg-surface-container-lowest">
                <p class="font-label-sm text-label-sm text-secondary uppercase tracking-widest">Low Stock</p>
                <p class="font-body-md text-on-surface-variant mt-xs">7 parts below reorder level</p>
            </div>
            <div class="border border-outline-variant p-md bg-surface-container-lowest">
                <p class="font-label-sm text-label-sm text-outline uppercase tracking-widest">Scheduled</p>
                <p class="font-body-md text-on-surface-variant mt-xs">3 unpaid invoices pending</p>
            </div>
        </div>
    </section>
</div>

{{-- SECTION 3: BOOKING SCHEDULE --}}
<section class="space-y-md pb-xl">
    <div class="flex justify-between items-end">
        <h2 class="font-headline-md text-headline-md text-primary tracking-tighter uppercase">Booking Schedule</h2>
        <span class="font-label-sm text-label-sm text-outline uppercase tracking-widest">{{ now()->format('M d, Y') }}</span>
    </div>
    <div class="overflow-x-auto no-scrollbar">
        <div class="flex gap-gutter">
            @forelse($todayBookings as $appt)
            <div class="w-80 bg-surface-container-low border border-outline-variant p-md flex flex-col gap-sm hover:border-primary transition-colors duration-200">
                <div class="flex flex-wrap items-center justify-between gap-xs border-b border-outline-variant pb-xs mb-xs">
                    <span class="font-label-sm text-label-sm text-secondary uppercase tracking-widest">{{ $appt->starts_at->format('h:i A') }}</span>
                    <div class="flex items-center gap-xs">
                        @if($appt->getCategoryKey())
                            @php
                                $catDetails = $appt->getCategoryDetails();
                                $colorClass = $categoryColors[$appt->getCategoryKey()] ?? 'border-outline-variant bg-surface-container-high text-primary';
                            @endphp
                            @if($catDetails)
                            <span class="rounded border px-sm py-0.5 text-[9px] font-label-sm uppercase tracking-widest inline-flex items-center gap-xs {{ $colorClass }}">
                                <span class="material-symbols-outlined text-[10px]">{{ $catDetails['icon'] }}</span>
                                {{ $catDetails['label'] }}
                            </span>
                            @endif
                        @endif
                        <span class="rounded border border-outline-variant bg-white px-sm py-0.5 text-[9px] font-label-sm uppercase tracking-widest text-primary">
                            {{ $appt->user_id ? 'Booking' : 'Walk In' }}
                        </span>
                    </div>
                </div>
                <div>
                    <h4 class="font-headline-sm text-lg font-bold uppercase text-primary">{{ $appt->customer_name }}</h4>
                    <p class="font-mono text-[9px] uppercase tracking-widest text-outline">
                        @if($appt->customer_phone) {{ $appt->customer_phone }} • @endif <span class="lowercase">{{ $appt->customer_email }}</span>
                    </p>
                    <p class="font-body-md text-sm text-on-surface-variant mt-xs">{{ $appt->service_name }}</p>
                </div>
                <div class="mt-auto pt-sm border-t border-outline-variant flex items-center justify-between">
                    <span class="font-label-sm text-[10px] uppercase text-outline">Vehicle</span>
                    <span class="px-2 py-1 bg-surface-container-highest border border-outline-variant font-label-sm text-[10px] uppercase text-primary">
                        {{ trim(($appt->bike_name ?? '') . ' ' . ($appt->model ?? '')) ?: 'Workshop Entry' }}
                    </span>
                </div>
            </div>
            @empty
                <p class="text-on-surface-variant">No bookings scheduled for today.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const refreshIntervalMs = 15000;

        const hasActiveFormInteraction = () => {
            const activeElement = document.activeElement;

            if (!activeElement) {
                return false;
            }

            return ['INPUT', 'SELECT', 'TEXTAREA'].includes(activeElement.tagName);
        };

        const shouldRefreshDashboard = () => {
            return document.visibilityState === 'visible' && !hasActiveFormInteraction();
        };

        setInterval(() => {
            if (shouldRefreshDashboard()) {
                window.location.reload();
            }
        }, refreshIntervalMs);
    })();
</script>
@endpush
