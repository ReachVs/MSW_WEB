@extends('layouts.admin')
@section('title', 'Inventory')
@php $page = 'inventory'; @endphp

@push('head')
<style>
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-thumb { background: #091426; }
</style>
@endpush

@section('content')
{{-- HEADER & KPI GRID --}}
<section>
    <div class="flex justify-between items-end mb-lg">
        <div>
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-secondary block mb-xs">Operational Overview</span>
            <h2 class="font-headline-lg text-headline-lg text-primary">Inventory Control</h2>
        </div>
        <div class="flex gap-md">
            <button class="px-xl py-sm border border-primary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary hover:text-on-primary transition-all">Export Manifest</button>
            <button class="px-xl py-sm bg-secondary text-on-secondary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary transition-all">Add New Part</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div class="p-md border border-primary bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Total Parts Count</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-primary">{{ number_format($kpi['totalParts']) }}</span>
                {{-- <span class="font-label-sm text-green-600">+12%</span> --}}
            </div>
        </div>
        <div class="p-md border border-secondary bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-secondary">Low Stock Alerts</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-secondary">{{ str_pad($kpi['lowStockParts'], 2, '0', STR_PAD_LEFT) }}</span>
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">warning</span>
            </div>
        </div>
        <div class="p-md border border-outline-variant bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Pending Orders</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-primary">{{ str_pad($kpi['pendingOrders'], 2, '0', STR_PAD_LEFT) }}</span>
                <span class="font-label-sm text-outline">Active</span>
            </div>
        </div>
        <div class="p-md border border-outline-variant bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Inventory Valuation</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-primary">${{ number_format($kpi['totalValuation'] / 100, 2) }}</span>
                <span class="font-label-sm text-outline">USD</span>
            </div>
        </div>
    </div>
</section>

{{-- CRITICAL STOCK --}}
<section class="grid grid-cols-12 gap-gutter">
    <div class="col-span-12 lg:col-span-5">
        <div class="border border-outline-variant p-lg bg-surface-container-low h-full">
            <h3 class="font-label-sm text-label-sm uppercase tracking-widest mb-lg border-b border-outline-variant pb-sm">Critical Stock Levels</h3>
            <div class="space-y-lg">
                @forelse($criticalParts as $item)
                <div>
                    <div class="flex justify-between mb-sm">
                        <span class="font-body-md font-bold">{{ $item->name }}</span>
                        <span class="font-label-sm text-secondary">{{ $item->stock_pct }}% Remaining</span>
                    </div>
                    <div class="w-full h-1 bg-outline-variant">
                        <div class="h-full bg-secondary" style="width: {{ $item->stock_pct }}%;"></div>
                    </div>
                </div>
                @empty
                    <p class="text-on-surface-variant">No critical stock items.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-span-12 lg:col-span-7">
        <div class="border border-outline-variant bg-surface-container-low h-full min-h-[300px] p-lg flex items-center justify-center">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Stock Chart — Coming Soon</span>
        </div>
    </div>
</section>

{{-- INVENTORY TABLE --}}
<section>
    <div class="border border-outline-variant bg-surface-container-lowest">
        <div class="p-md border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
            <div class="flex items-center gap-md flex-1">
                <span class="material-symbols-outlined text-outline">search</span>
                <input class="bg-transparent border-none focus:ring-0 w-full font-label-sm text-label-sm uppercase tracking-widest text-on-surface placeholder-outline"
                       placeholder="SEARCH REGISTRY BY SKU OR PART NAME..." type="text" id="inventorySearch">
            </div>
            <div class="flex gap-sm">
                <button class="p-xs hover:bg-surface-container transition-colors"><span class="material-symbols-outlined text-outline">filter_list</span></button>
                <button class="p-xs hover:bg-surface-container transition-colors"><span class="material-symbols-outlined text-outline">more_vert</span></button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant">
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Part Name</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">SKU ID</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Category</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Stock Level</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-right">Unit Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($parts as $part)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-md">
                            <div class="font-body-md font-bold text-primary">{{ $part->name }}</div>
                            <div class="font-label-sm text-outline">{{ $part->description }}</div>
                        </td>
                        <td class="px-md py-md font-label-sm text-on-surface-variant">{{ $part->sku }}</td>
                        <td class="px-md py-md">
                            <span class="px-sm py-1 border border-outline-variant font-label-sm uppercase text-[10px]">{{ $part->category }}</span>
                        </td>
                        <td class="px-md py-md">
                            <div class="flex items-center justify-center gap-sm">
                                <div class="w-16 h-1 bg-outline-variant">
                                    <div class="h-full {{ $part->stock_pct <= 24 ? 'bg-secondary' : 'bg-green-500' }}" style="width: {{ $part->stock_pct }}%;"></div>
                                </div>
                                <span class="font-label-sm {{ $part->stock_pct <= 24 ? 'text-secondary font-bold' : '' }}">{{ $part->stock_pct }}%</span>
                            </div>
                        </td>
                        <td class="px-md py-md text-right font-body-md font-bold text-primary">${{ number_format($part->unit_price_cents / 100, 2) }}</td>
                    </tr>
                    @empty
                        <tr><td colspan="5" class="px-md py-md text-on-surface-variant">No inventory parts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-md bg-surface-container-low border-t border-outline-variant flex justify-between items-center">
            <span class="font-label-sm text-outline uppercase">Showing {{ $parts->firstItem() }}–{{ $parts->lastItem() }} of {{ $parts->total() }} items</span>
            <div class="flex gap-unit">
                @if ($parts->onFirstPage())
                    <button class="px-md py-1 border border-outline-variant font-label-sm uppercase opacity-50 cursor-not-allowed">Prev</button>
                @else
                    <a href="{{ $parts->previousPageUrl() }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest">Prev</a>
                @endif

                @foreach ($parts->getUrlRange(1, $parts->lastPage()) as $page => $url)
                    @if ($page == $parts->currentPage())
                        <button class="px-md py-1 bg-primary text-on-primary font-label-sm uppercase">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($parts->hasMorePages())
                    <a href="{{ $parts->nextPageUrl() }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest">Next</a>
                @else
                    <button class="px-md py-1 border border-outline-variant font-label-sm uppercase opacity-50 cursor-not-allowed">Next</button>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.hover\\:-translate-y-0\\.5').forEach(card => {
        card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-2px)');
        card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
    });

    const search = document.getElementById('inventorySearch');
    if (search) {
        search.addEventListener('focus', () => search.parentElement.classList.add('ring-1', 'ring-secondary'));
        search.addEventListener('blur', () => search.parentElement.classList.remove('ring-1', 'ring-secondary'));
    }
</script>
@endpush
