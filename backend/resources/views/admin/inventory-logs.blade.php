@extends('layouts.admin')
@section('title', 'Stock Movement Logs')
@php $page = 'inventory'; @endphp

@section('content')
@fragment('content')
<div id="logs-content-container">
{{-- Breadcrumbs & Header --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-md mb-lg">
    <div>
        <nav class="flex items-center gap-2 mb-xs text-xs font-label-sm uppercase tracking-widest text-outline">
            <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="hover:text-primary transition-colors">Inventory</a>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-bold">Stock Movement Logs</span>
        </nav>
        <h2 class="font-headline-lg text-headline-lg text-primary">Stock Movement Logs</h2>
    </div>
    <div>
        <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="px-xl py-sm border border-primary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary hover:text-on-primary transition-all">Back to Dashboard</a>
    </div>
</div>

{{-- Logs Table --}}
<section class="border border-outline-variant bg-surface-container-lowest">
    <div class="p-md border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
        <span class="font-label-sm text-outline uppercase tracking-wider">All Historical Inventory Transactions</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-outline-variant">
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Timestamp</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Part Name</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">SKU ID</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Type</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Change Qty</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Stock level</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Operator</th>
                    <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant">
                @forelse($logs as $log)
                <tr class="hover:bg-surface-container-low transition-colors text-xs font-body-md">
                    <td class="px-md py-md whitespace-nowrap text-on-surface-variant">
                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                    </td>
                    <td class="px-md py-md font-bold text-primary">
                        <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory.show', $log->part_id) }}" class="hover:text-secondary hover:underline">
                            {{ $log->part->name }}
                        </a>
                    </td>
                    <td class="px-md py-md text-on-surface-variant font-label-sm uppercase text-[10px]">{{ $log->part->sku }}</td>
                    <td class="px-md py-md text-center">
                        @if($log->movement_type === 'Stock In')
                            <span class="rounded bg-green-100 px-2 py-0.5 font-bold text-green-700 uppercase text-[9px] tracking-wider">Stock In</span>
                        @elseif($log->movement_type === 'Stock Out')
                            <span class="rounded bg-secondary/15 px-2 py-0.5 font-bold text-secondary uppercase text-[9px] tracking-wider">Stock Out</span>
                        @elseif($log->movement_type === 'Service Usage')
                            <span class="rounded bg-blue-100 px-2 py-0.5 font-bold text-blue-700 uppercase text-[9px] tracking-wider">Service Usage</span>
                        @else
                            <span class="rounded bg-gray-100 px-2 py-0.5 font-bold text-outline uppercase text-[9px] tracking-wider">Adjustment</span>
                        @endif
                    </td>
                    <td class="px-md py-md text-center font-bold {{ $log->quantity > 0 ? 'text-green-600' : ($log->quantity < 0 ? 'text-secondary' : 'text-primary') }}">
                        {{ $log->quantity > 0 ? '+'.$log->quantity : $log->quantity }}
                    </td>
                    <td class="px-md py-md text-center text-on-surface-variant">
                        {{ $log->previous_stock }} &rarr; {{ $log->new_stock }}
                    </td>
                    <td class="px-md py-md text-on-surface-variant font-bold">
                        {{ $log->creator?->name ?? 'System / Engine' }}
                    </td>
                    <td class="px-md py-md text-on-surface-variant max-w-[250px] truncate" title="{{ $log->remarks }}">
                        {{ $log->remarks }}
                    </td>
                </tr>
                @empty
                    <tr><td colspan="8" class="px-md py-md text-on-surface-variant">No stock movement logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Controls --}}
    <div class="p-md bg-surface-container-low border-t border-outline-variant flex justify-between items-center">
        <span class="font-label-sm text-outline uppercase text-[11px]">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries</span>
        <div class="flex gap-unit">
            @if ($logs->onFirstPage())
                <button class="px-md py-1 border border-outline-variant font-label-sm uppercase opacity-50 cursor-not-allowed text-xs">Prev</button>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest text-xs">Prev</a>
            @endif

            @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                @if ($page == $logs->currentPage())
                    <button class="px-md py-1 bg-primary text-on-primary font-label-sm uppercase text-xs">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest text-xs">{{ $page }}</a>
                @endif
            @endforeach

            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest text-xs">Next</a>
            @else
                <button class="px-md py-1 border border-outline-variant font-label-sm uppercase opacity-50 cursor-not-allowed text-xs">Next</button>
            @endif
        </div>
    </div>
</section>
</div>
@endfragment
@endsection

@push('scripts')
<script>
    let isSyncing = false;
    let hasPendingSync = false;

    const inventorySyncChannelName = 'madape-inventory-sync';
    const inventorySyncStorageKey = 'madape-inventory-sync';
    const pageInstanceId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;

    const broadcastChannel = typeof BroadcastChannel !== 'undefined'
        ? new BroadcastChannel(inventorySyncChannelName)
        : null;

    async function syncLogs() {
        if (isSyncing) {
            return;
        }

        if (document.visibilityState !== 'visible') {
            hasPendingSync = true;
            return;
        }

        isSyncing = true;

        try {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');
                
                const newContent = doc.getElementById('logs-content-container');
                const currentContent = document.getElementById('logs-content-container');
                
                if (newContent && currentContent) {
                    if (currentContent.innerHTML !== newContent.innerHTML) {
                        currentContent.innerHTML = newContent.innerHTML;
                    }
                }
            }
        } catch (error) {
            console.warn('Logs real-time sync failed:', error);
        } finally {
            isSyncing = false;
        }
    }

    function notifyOtherInventoryTabs() {
        const payload = {
            type: 'inventory-updated',
            source: pageInstanceId,
            timestamp: Date.now()
        };

        if (broadcastChannel) {
            broadcastChannel.postMessage(payload);
        }

        try {
            localStorage.setItem(inventorySyncStorageKey, JSON.stringify(payload));
        } catch (error) {
            console.warn('Inventory sync storage broadcast failed:', error);
        }
    }

    function handleIncomingInventoryUpdate(payload) {
        if (!payload || payload.source === pageInstanceId || payload.type !== 'inventory-updated') {
            return;
        }

        syncLogs();
    }

    // On load, if there's a success flash message (just in case), notify other tabs
    const hasSuccessFlash = @json(session()->has('status_success'));
    if (hasSuccessFlash) {
        notifyOtherInventoryTabs();
    }

    // Listen for broadcast channel updates
    if (broadcastChannel) {
        broadcastChannel.addEventListener('message', (event) => {
            handleIncomingInventoryUpdate(event.data);
        });
    }

    // Listen for storage fallback updates
    window.addEventListener('storage', (event) => {
        if (event.key !== inventorySyncStorageKey || !event.newValue) {
            return;
        }

        try {
            handleIncomingInventoryUpdate(JSON.parse(event.newValue));
        } catch (error) {
            console.warn('Inventory sync storage payload parsing failed:', error);
        }
    });

    // Listen for visibility change to run deferred syncs
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && hasPendingSync) {
            hasPendingSync = false;
            syncLogs();
        }
    });

    // Poll every 3 seconds for updates as fallback
    setInterval(syncLogs, 3000);
</script>
@endpush
