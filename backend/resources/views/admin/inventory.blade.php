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
@fragment('content')
<div id="inventory-content-container">
{{-- 1. BREADCRUMBS & TOP BAR --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-md mb-lg">
    <div>
        <nav class="flex items-center gap-2 mb-xs text-xs font-label-sm uppercase tracking-widest text-outline">
            <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="hover:text-primary transition-colors">Inventory</a>
            
            @if(isset($selectedCategory))
                <span class="text-outline-variant">/</span>
                <span class="text-primary font-bold">{{ $selectedCategory->name }}</span>
            @elseif(request('search'))
                <span class="text-outline-variant">/</span>
                <span class="text-primary font-bold">Search Results</span>
            @elseif($mode === 'part_details')
                <span class="text-outline-variant">/</span>
                <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory', ['category_id' => $part->category_id]) }}" class="hover:text-primary transition-colors">{{ $part->category->name }}</a>
                <span class="text-outline-variant">/</span>
                <span class="text-primary font-bold">{{ $part->name }}</span>
            @endif
        </nav>

        <h2 class="font-headline-lg text-headline-lg text-primary">
            @if($mode === 'dashboard')
                Inventory Control
            @elseif($mode === 'parts_list')
                {{ $selectedCategory ? $selectedCategory->name . ' Category' : 'Parts Registry' }}
            @elseif($mode === 'part_details')
                {{ $part->name }}
            @endif
        </h2>
    </div>

    <div class="flex flex-wrap gap-sm">
        <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory.logs') }}" class="px-xl py-sm border border-primary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary hover:text-on-primary transition-all">Stock Logs</a>
        
        @if($mode === 'parts_list' && ($canManageInventory ?? true))
            <button onclick="openAddPartModal()" class="px-xl py-sm bg-secondary text-on-secondary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary transition-all">Add New Part</button>
        @endif
    </div>
</div>

{{-- 2. DASHBOARD MODE --}}
@if($mode === 'dashboard')
    {{-- KPIs Grid --}}
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-lg">
        <div class="p-md border border-primary bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Total Parts</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-primary">{{ number_format($kpi['totalParts']) }}</span>
            </div>
        </div>
        <div class="p-md border border-secondary bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">Low Stock Items</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-secondary">{{ str_pad($kpi['lowStockParts'], 2, '0', STR_PAD_LEFT) }}</span>
                <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">warning</span>
            </div>
        </div>
        <div class="p-md border border-error bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-error font-bold">Out of Stock Items</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-error">{{ str_pad($kpi['outOfStockParts'], 2, '0', STR_PAD_LEFT) }}</span>
                <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
            </div>
        </div>
        <div class="p-md border border-outline-variant bg-surface-container-lowest flex flex-col justify-between h-32 hover:-translate-y-0.5 transition-transform">
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">Total Valuation</span>
            <div class="flex items-baseline gap-xs">
                <span class="font-headline-md text-headline-md text-primary">${{ number_format($kpi['totalValuation'] / 100, 2) }}</span>
                <span class="font-label-sm text-outline">USD</span>
            </div>
        </div>
    </section>

    {{-- Search Form --}}
    <section class="border border-outline-variant bg-surface-container-low p-md mb-lg">
        <form method="GET" action="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="flex items-center gap-md">
            <span class="material-symbols-outlined text-outline flex-shrink-0">search</span>
            <input class="bg-transparent border-none focus:ring-0 w-full font-label-sm text-label-sm uppercase tracking-widest text-on-surface placeholder-outline"
                   placeholder="SEARCH PARTS BY SKU OR PART NAME..." type="text" name="search" id="inventorySearch">
            <button type="submit" class="px-md py-1 bg-primary text-white font-label-sm text-[11px] uppercase tracking-widest hover:bg-secondary transition-colors">Search</button>
        </form>
    </section>

    {{-- Category Cards Grid --}}
    <section class="mb-lg">
        <h3 class="font-label-sm text-label-sm uppercase tracking-widest mb-md text-outline">Browse Categories</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
            @php
                $catIcons = [
                    'Engine' => 'settings_suggest',
                    'Brakes' => 'slow_motion_video',
                    'Suspension' => 'tune',
                    'Drivetrain' => 'sync',
                    'Electrical' => 'bolt',
                    'Tyres' => 'album',
                    'Performance' => 'speed',
                    'Accessories' => 'handyman'
                ];
            @endphp
            @foreach($categories as $cat)
                <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory', ['category_id' => $cat->id]) }}" 
                   class="p-md border border-outline-variant bg-surface-container-lowest hover:border-secondary hover:shadow-sm flex flex-col justify-between h-44 group transition-all">
                    <div class="flex justify-between items-start">
                        <span class="material-symbols-outlined text-outline group-hover:text-secondary text-2xl transition-colors">
                            {{ $catIcons[$cat->name] ?? 'category' }}
                        </span>
                        <span class="px-sm py-0.5 border border-outline-variant font-label-sm text-[10px] uppercase text-outline group-hover:border-secondary group-hover:text-secondary transition-colors">
                            {{ $cat->parts_count }} Parts
                        </span>
                    </div>
                    <div class="mt-md">
                        <h4 class="font-headline-md text-lg text-primary font-bold group-hover:text-secondary transition-colors mb-xs">{{ $cat->name }}</h4>
                        <p class="font-body-md text-xs text-on-surface-variant line-clamp-2">{{ $cat->description }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Low Stock & Warning Banners --}}
    @if(count($criticalParts) > 0)
        <section class="border border-secondary bg-secondary/5 p-lg">
            <h3 class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold mb-md flex items-center gap-xs">
                <span class="material-symbols-outlined">warning</span> Critical Stock Attention Required
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
                @foreach($criticalParts as $cp)
                    <div class="p-sm bg-white border border-outline-variant flex justify-between items-center hover:border-secondary transition-colors">
                        <div class="min-w-0 pr-xs">
                            <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory.show', $cp->id) }}" class="font-body-md font-bold text-primary hover:text-secondary block truncate">{{ $cp->name }}</a>
                            <span class="font-label-sm text-outline text-[10px]">{{ $cp->sku }} (Min: {{ $cp->minimum_stock }})</span>
                        </div>
                        <span class="font-label-sm px-sm py-1 {{ $cp->stock_qty == 0 ? 'bg-error/15 text-error' : 'bg-secondary/15 text-secondary' }} font-bold uppercase shrink-0">
                            {{ $cp->stock_qty }} Units
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

{{-- 3. PARTS LIST MODE --}}
@elseif($mode === 'parts_list')
    <section class="border border-outline-variant bg-surface-container-lowest">
        {{-- Search & Filter Controls inside Category --}}
        <div class="p-md border-b border-outline-variant flex flex-col md:flex-row gap-sm justify-between items-center bg-surface-container-low">
            <form method="GET" action="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="flex items-center gap-md flex-1 w-full">
                @if(isset($selectedCategory))
                    <input type="hidden" name="category_id" value="{{ $selectedCategory->id }}">
                @endif
                <span class="material-symbols-outlined text-outline flex-shrink-0">search</span>
                <input class="bg-transparent border-none focus:ring-0 w-full font-label-sm text-label-sm uppercase tracking-widest text-on-surface placeholder-outline"
                       placeholder="SEARCH IN THIS CATEGORY BY SKU OR PART NAME..." type="text" name="search" value="{{ request('search') }}">
                
                <select name="category_id" onchange="this.form.submit()" class="border border-outline-variant bg-white px-md py-1.5 font-label-sm text-[11px] uppercase tracking-widest text-on-surface focus:outline-none focus:border-secondary mr-md shrink-0">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(isset($selectedCategory) && $selectedCategory->id == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id') || request('category'))
                    <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}" class="text-xs uppercase font-label-sm tracking-widest text-outline hover:text-primary mr-md shrink-0">Clear Filters</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-outline-variant">
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Part Name</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">SKU ID</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline">Category</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Status</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-center">Current Stock</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-right">Unit Price</th>
                        <th class="px-md py-sm font-label-sm text-label-sm uppercase tracking-widest text-outline text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @forelse($parts as $part)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-md">
                            <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory.show', $part->id) }}" class="font-body-md font-bold text-primary hover:text-secondary block">{{ $part->name }}</a>
                            <div class="font-label-sm text-outline text-[11px] line-clamp-1">{{ $part->description }}</div>
                        </td>
                        <td class="px-md py-md font-label-sm text-on-surface-variant">{{ $part->sku }}</td>
                        <td class="px-md py-md">
                            <span class="px-sm py-1 border border-outline-variant font-label-sm uppercase text-[10px]">{{ $part->category->name }}</span>
                        </td>
                        <td class="px-md py-md text-center">
                            @if($part->stock_status === 'Out of Stock')
                                <span class="rounded bg-error/15 px-2 py-0.5 text-[9px] font-bold text-error uppercase tracking-wider">Out of Stock</span>
                            @elseif($part->stock_status === 'Low Stock')
                                <span class="rounded bg-secondary/15 px-2 py-0.5 text-[9px] font-bold text-secondary uppercase tracking-wider">Low Stock</span>
                            @else
                                <span class="rounded bg-green-100 px-2 py-0.5 text-[9px] font-bold text-green-700 uppercase tracking-wider">In Stock</span>
                            @endif
                        </td>
                        <td class="px-md py-md text-center">
                            <span class="font-label-sm {{ $part->stock_qty <= $part->minimum_stock ? 'text-secondary font-bold' : 'text-primary' }}">{{ $part->stock_qty }} / {{ $part->minimum_stock }} Units</span>
                        </td>
                        <td class="px-md py-md text-right font-body-md font-bold text-primary">${{ number_format($part->unit_price, 2) }}</td>
                        <td class="px-md py-md text-right">
                            <div class="flex gap-sm justify-end">
                                <a href="{{ route(($portalRoutePrefix ?? 'admin').'.inventory.show', $part->id) }}" class="border border-primary px-sm py-1 font-label-sm text-[10px] uppercase tracking-widest hover:bg-primary hover:text-white transition-colors text-primary">View</a>
                                @if($canManageInventory ?? true)
                                <button onclick="openEditPartModal({{ json_encode($part) }})" class="border border-outline-variant px-sm py-1 font-label-sm text-[10px] uppercase tracking-widest hover:bg-surface-container-high transition-colors text-primary">Edit</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="7" class="px-md py-md text-on-surface-variant">No inventory parts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-md bg-surface-container-low border-t border-outline-variant flex justify-between items-center">
            <span class="font-label-sm text-outline uppercase text-[11px]">Showing {{ $parts->firstItem() }}–{{ $parts->lastItem() }} of {{ $parts->total() }} items</span>
            <div class="flex gap-unit">
                @if ($parts->onFirstPage())
                    <button class="px-md py-1 border border-outline-variant font-label-sm uppercase opacity-50 cursor-not-allowed">Prev</button>
                @else
                    <a href="{{ $parts->previousPageUrl() }}" class="px-md py-1 border border-outline-variant font-label-sm uppercase hover:bg-surface-container-lowest">Prev</a>
                @endif

                @foreach ($parts->getUrlRange(max(1, $parts->currentPage() - 2), min($parts->lastPage(), $parts->currentPage() + 2)) as $page => $url)
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
    </section>

{{-- 4. PART DETAILS MODE --}}
@elseif($mode === 'part_details')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        {{-- Profile/Detail Column --}}
        <div class="lg:col-span-5 space-y-md">
            <div class="border border-outline-variant p-lg bg-surface-container-lowest">
                <div class="flex justify-between items-start mb-md pb-sm border-b border-outline-variant/30">
                    <div>
                        <span class="px-sm py-1 border border-outline-variant font-label-sm uppercase text-[9px] text-outline block w-fit mb-xs">
                            {{ $part->category->name }}
                        </span>
                        <h3 class="font-headline-md text-primary font-bold text-2xl">{{ $part->name }}</h3>
                        <span class="font-label-sm text-outline text-[11px] block mt-xs">{{ $part->sku }}</span>
                    </div>

                    @if($part->stock_status === 'Out of Stock')
                        <span class="rounded bg-error/15 px-2.5 py-1 text-[10px] font-bold text-error uppercase tracking-wider">Out of Stock</span>
                    @elseif($part->stock_status === 'Low Stock')
                        <span class="rounded bg-secondary/15 px-2.5 py-1 text-[10px] font-bold text-secondary uppercase tracking-wider">Low Stock</span>
                    @else
                        <span class="rounded bg-green-100 px-2.5 py-1 text-[10px] font-bold text-green-700 uppercase tracking-wider">In Stock</span>
                    @endif
                </div>

                <div class="space-y-sm font-body-md text-sm mb-lg">
                    <p class="text-on-surface-variant border-b border-outline-variant/10 pb-xs">
                        <strong class="text-primary block font-label-sm text-[10px] uppercase text-outline">Description</strong> 
                        {{ $part->description ?: 'No description provided.' }}
                    </p>
                    <div class="grid grid-cols-2 gap-sm">
                        <div class="border-r border-outline-variant/10">
                            <span class="block font-label-sm text-[10px] uppercase text-outline">Current Stock</span>
                            <span class="text-lg font-bold text-primary">{{ $part->stock_qty }} Units</span>
                        </div>
                        <div>
                            <span class="block font-label-sm text-[10px] uppercase text-outline">Minimum Threshold</span>
                            <span class="text-lg font-bold text-primary">{{ $part->minimum_stock }} Units</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-sm border-t border-outline-variant/10 pt-sm">
                        <div class="border-r border-outline-variant/10">
                            <span class="block font-label-sm text-[10px] uppercase text-outline">Unit Price</span>
                            <span class="text-lg font-bold text-primary">${{ number_format($part->unit_price, 2) }}</span>
                        </div>
                        <div>
                            <span class="block font-label-sm text-[10px] uppercase text-outline">Valuation</span>
                            <span class="text-lg font-bold text-primary">${{ number_format(($part->stock_qty * $part->unit_price), 2) }}</span>
                        </div>
                    </div>
                </div>

                @if($canManageInventory ?? true)
                <div class="flex gap-sm border-t border-outline-variant pt-md">
                    <button onclick="openEditPartModal({{ json_encode($part) }})" class="flex-1 border border-primary py-2 font-label-sm uppercase text-xs tracking-wider text-primary hover:bg-surface-container-high transition-colors">Edit part profile</button>
                    <button type="button" onclick="openDeleteConfirmModal()" class="flex-1 border border-error py-2 font-label-sm uppercase text-xs tracking-wider text-error hover:bg-error hover:text-white transition-colors">Delete Part</button>
                </div>
                @endif
            </div>

            @if($canManageInventory ?? true)
            {{-- Quick Actions Box --}}
            <div class="border border-outline-variant p-lg bg-surface-container-lowest">
                <h4 class="font-label-sm text-label-sm uppercase tracking-widest text-outline mb-md border-b border-outline-variant/30 pb-xs">Stock Operations</h4>
                <div class="grid grid-cols-3 gap-sm">
                    <button onclick="openStockInModal()" class="flex flex-col items-center justify-center p-sm border border-outline-variant hover:border-secondary hover:text-secondary transition-all">
                        <span class="material-symbols-outlined text-2xl mb-xs">add_box</span>
                        <span class="font-label-sm text-[10px] uppercase tracking-wider font-bold">Stock In</span>
                    </button>
                    <button onclick="openStockOutModal()" class="flex flex-col items-center justify-center p-sm border border-outline-variant hover:border-secondary hover:text-secondary transition-all">
                        <span class="material-symbols-outlined text-2xl mb-xs">indeterminate_check_box</span>
                        <span class="font-label-sm text-[10px] uppercase tracking-wider font-bold">Stock Out</span>
                    </button>
                    <button onclick="openAdjustModal()" class="flex flex-col items-center justify-center p-sm border border-outline-variant hover:border-secondary hover:text-secondary transition-all">
                        <span class="material-symbols-outlined text-2xl mb-xs">tune</span>
                        <span class="font-label-sm text-[10px] uppercase tracking-wider font-bold">Adjust</span>
                    </button>
                </div>
            </div>
            @endif
        </div>

        {{-- Movement Logs Column --}}
        <div class="lg:col-span-7">
            <div class="border border-outline-variant p-lg bg-surface-container-lowest h-full min-h-[500px]">
                <h3 class="font-label-sm text-label-sm uppercase tracking-widest text-outline mb-md border-b border-outline-variant pb-sm">Stock Movement History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-outline-variant text-outline">
                                <th class="py-sm font-label-sm uppercase">Date</th>
                                <th class="py-sm font-label-sm uppercase">Type</th>
                                <th class="py-sm font-label-sm uppercase text-center">Qty</th>
                                <th class="py-sm font-label-sm uppercase">User</th>
                                <th class="py-sm font-label-sm uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/30">
                            @forelse($part->movements as $m)
                                <tr>
                                    <td class="py-sm text-on-surface-variant">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="py-sm">
                                        @if($m->movement_type === 'Stock In')
                                            <span class="text-green-700 font-bold">Stock In</span>
                                        @elseif($m->movement_type === 'Stock Out')
                                            <span class="text-secondary font-bold">Stock Out</span>
                                        @elseif($m->movement_type === 'Service Usage')
                                            <span class="text-blue-700 font-bold">Service Usage</span>
                                        @else
                                            <span class="text-outline font-bold">Adjustment</span>
                                        @endif
                                    </td>
                                    <td class="py-sm text-center font-bold {{ $m->quantity > 0 ? 'text-green-600' : ($m->quantity < 0 ? 'text-secondary' : 'text-primary') }}">
                                        {{ $m->quantity > 0 ? '+'.$m->quantity : $m->quantity }}
                                    </td>
                                    <td class="py-sm text-on-surface-variant">{{ $m->creator?->name ?? 'System' }}</td>
                                    <td class="py-sm text-on-surface-variant max-w-[200px] truncate" title="{{ $m->remarks }}">{{ $m->remarks }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-sm text-on-surface-variant">No movement history logs.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
@endfragment

{{-- ========================================== MODALS ========================================== --}}

@if($canManageInventory ?? true)
{{-- ADD PART MODAL --}}
<div id="addPartModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
    <div class="bg-white border border-primary w-full max-w-lg p-lg space-y-md">
        <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
            <h3 class="font-headline-md text-primary uppercase">Add New Inventory Part</h3>
            <button onclick="closeAddPartModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
        </div>
        <form method="POST" action="{{ route('admin.inventory.store') }}" class="space-y-sm">
            @csrf
            <div>
                <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Name</label>
                <input type="text" name="name" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
            </div>
            <div>
                <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Description</label>
                <textarea name="description" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm h-20"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-sm">
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">SKU ID</label>
                    <input type="text" name="sku" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Category</label>
                    <select name="category_id" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(isset($selectedCategory) && $selectedCategory->id == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-sm">
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Stock Qty</label>
                    <input type="number" name="stock_qty" min="0" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required value="10">
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Min Stock</label>
                    <input type="number" name="minimum_stock" min="0" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required value="5">
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Unit Price ($)</label>
                    <input type="number" step="0.01" min="0" name="unit_price" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required placeholder="0.00">
                </div>
            </div>
            <div class="flex gap-sm pt-md border-t border-outline-variant">
                <button type="button" onclick="closeAddPartModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-sm hover:bg-surface-container-high transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-primary text-white py-2 font-label-sm uppercase text-sm hover:bg-secondary transition-colors">Save Part</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT PART MODAL --}}
<div id="editPartModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
    <div class="bg-white border border-primary w-full max-w-lg p-lg space-y-md">
        <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
            <h3 class="font-headline-md text-primary uppercase">Edit Inventory Part</h3>
            <button onclick="closeEditPartModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
        </div>
        <form id="editPartForm" method="POST" action="" class="space-y-sm">
            @csrf
            @method('PATCH')
            <div>
                <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Name</label>
                <input type="text" name="name" id="edit_name" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
            </div>
            <div>
                <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Description</label>
                <textarea name="description" id="edit_description" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm h-20"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-sm">
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">SKU ID</label>
                    <input type="text" name="sku" id="edit_sku" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Category</label>
                    <select name="category_id" id="edit_category_id" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-sm">
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Stock Qty</label>
                    <input type="number" name="stock_qty" id="edit_stock_qty" min="0" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Min Stock</label>
                    <input type="number" name="minimum_stock" id="edit_minimum_stock" min="0" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Unit Price ($)</label>
                    <input type="number" step="0.01" min="0" name="unit_price" id="edit_unit_price" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
            </div>
            <div class="flex gap-sm pt-md border-t border-outline-variant">
                <button type="button" onclick="closeEditPartModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-sm hover:bg-surface-container-high transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-primary text-white py-2 font-label-sm uppercase text-sm hover:bg-secondary transition-colors">Update Part</button>
            </div>
        </form>
    </div>
</div>

@if($mode === 'part_details')
    {{-- STOCK IN MODAL --}}
    <div id="stockInModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
        <div class="bg-white border border-primary w-full max-w-md p-lg space-y-md">
            <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
                <h3 class="font-headline-md text-primary uppercase">Stock Intake (Stock In)</h3>
                <button onclick="closeStockInModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.stock-in', $part->id) }}" class="space-y-sm">
                @csrf
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Quantity to Add</label>
                    <input type="number" name="quantity" min="1" value="1" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Remarks / Invoice Reference</label>
                    <input type="text" name="remarks" placeholder="Supplier delivery, invoice #..." class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm">
                </div>
                <div class="flex gap-sm pt-md border-t border-outline-variant">
                    <button type="button" onclick="closeStockInModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-xs hover:bg-surface-container-high transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-primary text-white py-2 font-label-sm uppercase text-xs hover:bg-secondary transition-colors">Confirm Stock In</button>
                </div>
            </form>
        </div>
    </div>

    {{-- STOCK OUT MODAL --}}
    <div id="stockOutModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
        <div class="bg-white border border-primary w-full max-w-md p-lg space-y-md">
            <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
                <h3 class="font-headline-md text-primary uppercase">Stock Dispatch (Stock Out)</h3>
                <button onclick="closeStockOutModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.stock-out', $part->id) }}" class="space-y-sm" onsubmit="return validateStockOut()">
                @csrf
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Quantity to Deduct (Max: {{ $part->stock_qty }})</label>
                    <input type="number" name="quantity" id="stockOutQty" min="1" max="{{ $part->stock_qty }}" value="1" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                    <p id="stockOutError" class="text-error font-body-md text-xs mt-1 hidden">Quantity exceeds currently available stock.</p>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Remarks / Usage Details</label>
                    <input type="text" name="remarks" placeholder="Manual dispatch, damaged part..." class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm">
                </div>
                <div class="flex gap-sm pt-md border-t border-outline-variant">
                    <button type="button" onclick="closeStockOutModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-xs hover:bg-surface-container-high transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-primary text-white py-2 font-label-sm uppercase text-xs hover:bg-secondary transition-colors">Confirm Stock Out</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADJUST MODAL --}}
    <div id="adjustModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
        <div class="bg-white border border-primary w-full max-w-md p-lg space-y-md">
            <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
                <h3 class="font-headline-md text-primary uppercase">Stock Level Adjustment</h3>
                <button onclick="closeAdjustModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
            </div>
            <form method="POST" action="{{ route('admin.inventory.adjust', $part->id) }}" class="space-y-sm">
                @csrf
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">New Absolute Quantity</label>
                    <input type="number" name="quantity" min="0" value="{{ $part->stock_qty }}" class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div>
                    <label class="block font-label-sm uppercase tracking-widest text-outline text-[10px]">Reason for Adjustment</label>
                    <input type="text" name="remarks" placeholder="Cycle count variance, auditing..." class="mt-xs w-full border border-outline-variant bg-white px-sm py-2 font-body-md text-sm" required>
                </div>
                <div class="flex gap-sm pt-md border-t border-outline-variant">
                    <button type="button" onclick="closeAdjustModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-xs hover:bg-surface-container-high transition-colors">Cancel</button>
                    <button type="submit" class="flex-1 bg-primary text-white py-2 font-label-sm uppercase text-xs hover:bg-secondary transition-colors">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
@endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if($mode === 'part_details')
    <div id="deleteConfirmModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-md">
        <div class="bg-white border border-error w-full max-w-md p-lg space-y-md shadow-2xl rounded-sm">
            <div class="flex justify-between items-center border-b border-outline-variant pb-sm">
                <div class="flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined font-bold text-error">warning</span>
                    <h3 class="font-headline-md uppercase tracking-wider text-sm font-bold">Confirm Deletion</h3>
                </div>
                <button type="button" onclick="closeDeleteConfirmModal()" class="material-symbols-outlined text-outline hover:text-primary">close</button>
            </div>
            <div class="space-y-sm">
                <p class="font-body-md text-sm text-outline">
                    Are you sure you want to delete the inventory part <span class="font-bold text-primary">"{{ $part->name }}"</span> (SKU: <span class="font-bold text-primary">{{ $part->sku }}</span>)?
                </p>
                <div class="bg-error/10 border-l-4 border-error p-sm">
                    <p class="text-xs text-error font-medium">
                        This action is permanent and cannot be undone. All stock records, movement logs, and historical transactions related to this part will be lost.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.inventory.destroy', $part->id) }}" class="flex gap-sm pt-md border-t border-outline-variant">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteConfirmModal()" class="flex-1 border border-outline-variant py-2 font-label-sm uppercase text-xs hover:bg-surface-container-high transition-colors">Cancel</button>
                <button type="submit" class="flex-1 bg-error text-white py-2 font-label-sm uppercase text-xs hover:bg-error-container hover:text-on-error-container transition-colors font-bold tracking-wider">Delete Permanently</button>
            </form>
        </div>
    </div>
    @endif
@endif {{-- end canManageInventory --}}

@endsection

@push('scripts')
<script>
    const search = document.getElementById('inventorySearch');
    if (search) {
        search.addEventListener('focus', () => search.parentElement.classList.add('ring-1', 'ring-secondary'));
        search.addEventListener('blur', () => search.parentElement.classList.remove('ring-1', 'ring-secondary'));
    }

    function openAddPartModal() {
        document.getElementById('addPartModal').classList.remove('hidden');
    }
    function closeAddPartModal() {
        document.getElementById('addPartModal').classList.add('hidden');
    }
    function openEditPartModal(part) {
        document.getElementById('editPartForm').action = `/admin/inventory/${part.id}`;
        
        document.getElementById('edit_name').value = part.name;
        document.getElementById('edit_description').value = part.description || '';
        document.getElementById('edit_sku').value = part.sku;
        document.getElementById('edit_category_id').value = part.category_id;
        document.getElementById('edit_stock_qty').value = part.stock_qty;
        document.getElementById('edit_minimum_stock').value = part.minimum_stock;
        document.getElementById('edit_unit_price').value = (part.unit_price_cents / 100).toFixed(2);
        
        document.getElementById('editPartModal').classList.remove('hidden');
    }
    function closeEditPartModal() {
        document.getElementById('editPartModal').classList.add('hidden');
    }

    @if($mode === 'part_details')
    function openStockInModal() {
        document.getElementById('stockInModal').classList.remove('hidden');
    }
    function closeStockInModal() {
        document.getElementById('stockInModal').classList.add('hidden');
    }
    function openStockOutModal() {
        document.getElementById('stockOutModal').classList.remove('hidden');
    }
    function closeStockOutModal() {
        document.getElementById('stockOutModal').classList.add('hidden');
    }
    function openAdjustModal() {
        document.getElementById('adjustModal').classList.remove('hidden');
    }
    function closeAdjustModal() {
        document.getElementById('adjustModal').classList.add('hidden');
    }
    function openDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }
    function closeDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }
    function validateStockOut() {
        const qtyInput = document.getElementById('stockOutQty');
        const maxQty = parseInt(qtyInput.max);
        const qtyVal = parseInt(qtyInput.value);
        const errEl = document.getElementById('stockOutError');

        if (qtyVal > maxQty) {
            errEl.classList.remove('hidden');
            return false;
        }
        errEl.classList.add('hidden');
        return true;
    }
    @endif

    // Real-time synchronization
    let isSyncing = false;
    let hasPendingSync = false;

    const inventorySyncChannelName = 'madape-inventory-sync';
    const inventorySyncStorageKey = 'madape-inventory-sync';
    const pageInstanceId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;

    const broadcastChannel = typeof BroadcastChannel !== 'undefined'
        ? new BroadcastChannel(inventorySyncChannelName)
        : null;

    function isModalOpen() {
        const modals = ['addPartModal', 'editPartModal', 'stockInModal', 'stockOutModal', 'adjustModal', 'deleteConfirmModal'];
        for (const id of modals) {
            const el = document.getElementById(id);
            if (el && !el.classList.contains('hidden')) {
                return true;
            }
        }
        return false;
    }

    function isUserInteracting() {
        const active = document.activeElement;
        if (!active) return false;
        const tag = active.tagName.toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select';
    }

    async function syncInventory() {
        if (isSyncing || isModalOpen() || isUserInteracting()) {
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

            if (response.status === 404) {
                // The part was deleted. Redirect to the list view.
                window.location.href = "{{ route(($portalRoutePrefix ?? 'admin').'.inventory') }}";
                return;
            }

            if (response.ok) {
                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');
                
                const newContent = doc.getElementById('inventory-content-container');
                const currentContent = document.getElementById('inventory-content-container');
                
                if (newContent && currentContent) {
                    if (currentContent.innerHTML !== newContent.innerHTML) {
                        currentContent.innerHTML = newContent.innerHTML;
                        rebindSyncInteractions();
                    }
                }
            }
        } catch (error) {
            console.warn('Inventory real-time sync failed:', error);
        } finally {
            isSyncing = false;
        }
    }

    function rebindSyncInteractions() {
        const search = document.getElementById('inventorySearch');
        if (search) {
            search.addEventListener('focus', () => search.parentElement.classList.add('ring-1', 'ring-secondary'));
            search.addEventListener('blur', () => search.parentElement.classList.remove('ring-1', 'ring-secondary'));
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

        syncInventory();
    }

    // On load, if there's a success flash message, notify other tabs
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
            syncInventory();
        }
    });

    // Poll every 3 seconds as a background fallback
    setInterval(syncInventory, 3000);
</script>
@endpush
