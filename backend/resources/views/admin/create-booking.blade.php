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
            <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-lg" id="walkin-form">
                @csrf

                {{-- CUSTOMER + MOTORCYCLE --}}
                <div class="grid gap-gutter md:grid-cols-2">
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Customer Information</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Name</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Email</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                placeholder="Optional for walk-in">
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Customer Phone</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                placeholder="Optional for walk-in">
                        </div>
                    </div>

                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Motorcycle Information</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Bike Brand</label>
                            <input type="text" name="bike_name" value="{{ old('bike_name') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Model</label>
                            <input type="text" name="model" value="{{ old('model') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Plate Number</label>
                            <input type="text" name="plate_number" value="{{ old('plate_number') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Engine Capacity</label>
                            <input type="text" name="engine_capacity" value="{{ old('engine_capacity') }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm"
                                placeholder="e.g. 1000">
                        </div>
                    </div>
                </div>

                {{-- ─────────────────────────────────────────────────── --}}
                {{-- SERVICE & STATUS — Catalog-style 3-tier hierarchy   --}}
                {{-- ─────────────────────────────────────────────────── --}}
                <div class="space-y-sm">
                    <div class="flex items-center gap-sm border-b border-outline-variant pb-sm">
                        <span class="material-symbols-outlined text-secondary text-[18px]">category</span>
                        <p class="font-label-sm uppercase tracking-widest text-outline">Service & Status</p>
                    </div>

                    {{-- Hidden inputs populated by JS --}}
                    <div id="service-hidden-inputs"></div>

                    {{-- MAIN CATEGORY CARDS --}}
                    <div class="grid grid-cols-2 gap-md lg:grid-cols-4" id="main-cat-grid">
                        @php
                            $mainCategories = [
                                ['key' => 'maintenance',   'name' => 'Maintenance',    'icon' => 'build',         'desc' => 'Chains, fluids, brakes & safety.'],
                                ['key' => 'washing',       'name' => 'Washing',        'icon' => 'wash',          'desc' => 'Foam wash, detail & protection.'],
                                ['key' => 'engine_checkup','name' => 'Engine Check',   'icon' => 'monitor_heart', 'desc' => 'Diagnostics & compression tests.'],
                                ['key' => 'tuning',        'name' => 'Tuning',         'icon' => 'speed',         'desc' => 'Dyno, ECU & performance remap.'],
                            ];
                            $catImages = [
                                'maintenance'    => '/mechanic-chain.png',
                                'washing'        => '/motorcycle-wash.png',
                                'engine_checkup' => '/mechanic-diagnostic.png',
                                'tuning'         => '/dyno-tuning.jpg',
                            ];
                        @endphp

                        @foreach($mainCategories as $cat)
                            <button
                                type="button"
                                data-main-cat="{{ $cat['key'] }}"
                                onclick="WalkIn.toggleMain('{{ $cat['key'] }}')"
                                id="main-btn-{{ $cat['key'] }}"
                                class="main-cat-btn group flex flex-col text-left border-2 border-outline-variant bg-white transition-all duration-200 overflow-hidden hover:border-secondary w-full"
                            >
                                <div class="relative w-full overflow-hidden" style="aspect-ratio:16/9">
                                    <img src="{{ $catImages[$cat['key']] }}" alt="{{ $cat['name'] }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    <div class="absolute bottom-2 right-2 bg-white p-1.5 border border-outline-variant shadow-sm flex items-center justify-center">
                                        <span class="material-symbols-outlined text-secondary text-xl">{{ $cat['icon'] }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow p-sm flex flex-col gap-xs">
                                    <span class="font-headline-lg text-sm font-black uppercase tracking-tight text-primary cat-name-text">{{ $cat['name'] }}</span>
                                    <span class="text-[10px] leading-relaxed text-on-surface-variant cat-desc-text">{{ $cat['desc'] }}</span>
                                    <div class="mt-auto pt-xs border-t border-outline-variant/30 flex justify-between items-center">
                                        <span class="font-mono text-[9px] font-bold uppercase tracking-wider text-outline cat-count-label" data-cat="{{ $cat['key'] }}">— subcategories</span>
                                        <span class="material-symbols-outlined text-secondary text-sm group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>

                    {{-- EXPANDED SUBCATEGORY + SERVICES AREA --}}
                    <div id="subcategory-area" class="hidden space-y-sm">
                        {{-- Back bar --}}
                        <div class="flex items-center gap-sm">
                            <button type="button" onclick="WalkIn.collapseMain()"
                                class="material-symbols-outlined text-outline hover:text-primary transition-colors text-xl">arrow_back</button>
                            <h3 id="expanded-cat-title" class="font-headline-lg text-lg uppercase font-bold text-primary"></h3>
                        </div>
                        <div id="subcategory-list" class="space-y-sm"></div>
                    </div>

                    {{-- Loading state --}}
                    <div id="catalog-loading" class="hidden py-md text-center">
                        <span class="font-mono text-xs uppercase tracking-widest text-outline">Loading catalog...</span>
                    </div>
                </div>

                {{-- STATUS & MECHANIC --}}
                <div class="grid gap-gutter md:grid-cols-2">
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Starting Status</p>
                        <div class="grid grid-cols-3 gap-sm" id="status-buttons">
                            @php
                                $statusOptions = [
                                    ['value' => 'pending',   'label' => 'Pending',   'icon' => 'schedule',      'color' => 'border-[#6B7280] text-[#6B7280] bg-[#6B7280]/5',  'active' => 'border-[#6B7280] bg-[#6B7280] text-white'],
                                    ['value' => 'confirmed', 'label' => 'Confirmed', 'icon' => 'check_circle',  'color' => 'border-[#1D4ED8] text-[#1D4ED8] bg-[#1D4ED8]/5', 'active' => 'border-[#1D4ED8] bg-[#1D4ED8] text-white'],
                                    ['value' => 'repair',    'label' => 'In Repair', 'icon' => 'build',         'color' => 'border-[#B45309] text-[#B45309] bg-[#B45309]/5', 'active' => 'border-[#B45309] bg-[#B45309] text-white'],
                                ];
                            @endphp
                            @foreach($statusOptions as $opt)
                                <button
                                    type="button"
                                    data-status="{{ $opt['value'] }}"
                                    onclick="WalkIn.setStatus('{{ $opt['value'] }}')"
                                    class="status-btn flex flex-col items-center gap-xs border-2 px-sm py-sm transition-all duration-150 {{ $opt['color'] }} {{ old('status', 'confirmed') === $opt['value'] ? $opt['active'] : '' }}"
                                    data-color="{{ $opt['color'] }}"
                                    data-active-color="{{ $opt['active'] }}"
                                >
                                    <span class="material-symbols-outlined text-[20px]">{{ $opt['icon'] }}</span>
                                    <span class="font-mono text-[10px] font-bold uppercase tracking-widest">{{ $opt['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="status" id="status-hidden" value="{{ old('status', 'confirmed') }}" required>
                    </div>

                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Assign Mechanic</p>
                        <select name="mechanic_id" class="w-full border border-outline-variant bg-white px-sm py-sm font-label-sm uppercase tracking-widest">
                            <option value="">— Unassigned</option>
                            @foreach($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}" @selected((string) old('mechanic_id') === (string) $mechanic->id)>
                                    {{ $mechanic->name }}@if($mechanic->specialization) • {{ $mechanic->specialization }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SCHEDULE --}}
                <div class="grid gap-gutter md:grid-cols-2">
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Schedule</p>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Booking Date</label>
                            <input type="date" name="booking_date"
                                value="{{ old('booking_date', $defaultDate->toDateString()) }}"
                                class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                        </div>
                        <div>
                            <label class="block font-label-sm uppercase tracking-widest text-outline">Time Slot</label>
                            <select name="booking_time" class="mt-xs w-full border border-outline-variant bg-white px-sm py-sm" required>
                                @foreach($timeSlots as $slot)
                                    <option value="{{ $slot['value'] }}" @selected(old('booking_time') === $slot['value'])>
                                        {{ $slot['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="space-y-sm">
                        <p class="font-label-sm uppercase tracking-widest text-outline">Customer Description / Notes</p>
                        <textarea name="notes" rows="5"
                            class="w-full border border-outline-variant bg-white px-sm py-sm"
                            placeholder="Describe the customer request, issue, or workshop notes.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex flex-wrap gap-sm border-t border-outline-variant pt-lg">
                    <button class="bg-primary px-lg py-sm font-label-sm uppercase tracking-widest text-on-primary hover:bg-secondary transition-colors">
                        Save Walk-In Entry
                    </button>
                    <a href="{{ route('admin.queue') }}"
                       class="border border-outline-variant bg-white px-lg py-sm font-label-sm uppercase tracking-widest text-primary hover:bg-surface-container-high transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- SIDEBAR --}}
        <aside class="space-y-lg">
            {{-- Live Selection Summary --}}
            <div class="border border-outline-variant bg-surface-container-lowest p-md" id="selection-summary-panel">
                <div class="flex items-center gap-sm border-b border-outline-variant pb-sm mb-sm">
                    <span class="material-symbols-outlined text-secondary text-[16px]">receipt_long</span>
                    <h2 class="font-headline-md text-primary uppercase text-sm tracking-widest">Selection Summary</h2>
                </div>
                <div id="summary-empty" class="text-center py-md">
                    <span class="material-symbols-outlined text-outline text-3xl block">playlist_add</span>
                    <p class="font-mono text-[10px] uppercase tracking-widest text-outline mt-xs">No services selected</p>
                </div>
                <div id="summary-items" class="hidden space-y-xs mb-sm"></div>
                <div id="summary-total" class="hidden border-t border-outline-variant pt-sm flex justify-between items-baseline">
                    <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-outline">Total</span>
                    <span id="summary-total-amount" class="font-mono text-xl font-bold text-secondary">$0.00</span>
                </div>
            </div>

            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <h2 class="font-headline-md text-primary uppercase text-sm tracking-widest">How It Works</h2>
                <div class="mt-md space-y-sm text-sm text-on-surface-variant">
                    <div class="flex gap-sm">
                        <span class="material-symbols-outlined text-secondary text-[16px] mt-0.5 shrink-0">person</span>
                        <p>Enter walk-in customer and motorcycle details.</p>
                    </div>
                    <div class="flex gap-sm">
                        <span class="material-symbols-outlined text-secondary text-[16px] mt-0.5 shrink-0">category</span>
                        <p>Pick a main category, then expand a sub-category to select services.</p>
                    </div>
                    <div class="flex gap-sm">
                        <span class="material-symbols-outlined text-secondary text-[16px] mt-0.5 shrink-0">tune</span>
                        <p>Set the starting status and optionally assign a mechanic.</p>
                    </div>
                    <div class="flex gap-sm">
                        <span class="material-symbols-outlined text-secondary text-[16px] mt-0.5 shrink-0">event</span>
                        <p>Choose date, time slot and save directly into the queue.</p>
                    </div>
                </div>
            </div>

            <div class="border border-outline-variant bg-surface-container-lowest p-md">
                <h2 class="font-headline-md text-primary uppercase text-sm tracking-widest">Schedule Note</h2>
                <p class="mt-sm text-sm text-on-surface-variant">
                    If the selected time slot is already full, the system will stop the save and ask you to choose a different slot.
                </p>
            </div>
        </aside>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    // ─── State ───────────────────────────────────────────────────────────────
    let catalogData     = null;
    let expandedMain    = null;
    let expandedSub     = null;  // tracks a single open sub at a time
    let selectedServices = {};   // { id: { id, name, price, main_category } }
    const serviceIndex  = {};   // Central registry to map service IDs to objects

    const mainCategoryMeta = {
        maintenance:    { name: 'Maintenance Services',  icon: 'build' },
        washing:        { name: 'Washing Services',      icon: 'wash' },
        engine_checkup: { name: 'Engine Check Up',       icon: 'monitor_heart' },
        tuning:         { name: 'Tuning Performance',    icon: 'speed' },
    };

    const oldServiceIds = @json(old('service_ids', []));

    // ─── DOM refs ─────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const subcatArea     = $('subcategory-area');
    const subcatList     = $('subcategory-list');
    const catTitle       = $('expanded-cat-title');
    const catalogLoading = $('catalog-loading');
    const hiddenInputs   = $('service-hidden-inputs');

    // ─── Fetch catalog ────────────────────────────────────────────────────────
    async function loadCatalog() {
        if (catalogData) return;
        catalogLoading.classList.remove('hidden');
        try {
            const res  = await fetch('/api/services', { headers: { Accept: 'application/json' } });
            const body = await res.json();
            catalogData = body.data || {};

            // Build index of all services & restore old values
            Object.keys(mainCategoryMeta).forEach(catKey => {
                const cat = catalogData[catKey];
                if (cat && cat.subcategories) {
                    Object.values(cat.subcategories).forEach(sub => {
                        if (sub.items) {
                            sub.items.forEach(item => {
                                serviceIndex[item.id] = item;
                                if (oldServiceIds.includes(Number(item.id)) || oldServiceIds.includes(String(item.id))) {
                                    selectedServices[item.id] = { ...item, main_category: catKey };
                                }
                            });
                        }
                    });
                }
            });

            // Update subcategory count labels
            Object.keys(mainCategoryMeta).forEach(key => {
                const el = document.querySelector(`.cat-count-label[data-cat="${key}"]`);
                if (el && catalogData[key]?.subcategories) {
                    const count = Object.keys(catalogData[key].subcategories).length;
                    el.textContent = `${count} subcategor${count === 1 ? 'y' : 'ies'}`;
                }
            });

            updateHiddenInputs();
            updateSummary();
        } catch(e) {
            console.error('Catalog fetch failed', e);
        } finally {
            catalogLoading.classList.add('hidden');
        }
    }

    // ─── Main category toggle ─────────────────────────────────────────────────
    async function toggleMain(key) {
        await loadCatalog();
        if (expandedMain === key) { collapseMain(); return; }
        expandedMain = key;
        expandedSub  = null;

        // Style active card
        document.querySelectorAll('.main-cat-btn').forEach(btn => {
            const isActive = btn.dataset.mainCat === key;
            btn.classList.toggle('border-primary', isActive);
            btn.classList.toggle('bg-primary', isActive);
            btn.classList.toggle('border-outline-variant', !isActive);
            btn.classList.toggle('bg-white', !isActive);
            btn.querySelectorAll('.cat-name-text, .cat-desc-text, .cat-count-label').forEach(el => {
                el.classList.toggle('text-white', isActive);
                el.classList.toggle('text-white/80', isActive);
                el.classList.toggle('text-white/60', isActive);
            });
        });

        catTitle.textContent = mainCategoryMeta[key]?.name || key;
        renderSubcategories(key);
        subcatArea.classList.remove('hidden');
    }

    function collapseMain() {
        expandedMain = null;
        expandedSub  = null;
        subcatArea.classList.add('hidden');
        subcatList.innerHTML = '';
        document.querySelectorAll('.main-cat-btn').forEach(btn => {
            btn.classList.remove('border-primary', 'bg-primary');
            btn.classList.add('border-outline-variant', 'bg-white');
            btn.querySelectorAll('.cat-name-text').forEach(el => {
                el.classList.remove('text-white', 'text-white/80', 'text-white/60');
            });
        });
    }

    // ─── Render subcategories ──────────────────────────────────────────────────
    function renderSubcategories(mainKey) {
        subcatList.innerHTML = '';
        if (!catalogData?.[mainKey]?.subcategories) return;

        const isWashing = mainKey === 'washing';
        const subs = catalogData[mainKey].subcategories;

        Object.entries(subs)
            .filter(([sk]) => sk !== '_root' && sk !== 'General' && sk !== 'General Maintenance')
            .forEach(([subKey, subData]) => {
                const items     = subData.items || [];
                const selectable = items.filter(i => i.selection_mode === 1);
                if (selectable.length === 0) return;

                const headerItem = items.find(i => i.selection_mode === 0) || items[0];
                const card = document.createElement('div');
                card.className = 'border border-outline-variant bg-white overflow-hidden transition-colors';
                card.dataset.subKey = subKey;

                if (isWashing) {
                    // Washing: single-select package row
                    const packagePrice = selectable.reduce((s, i) => s + (parseFloat(i.price) || 0), 0);
                    const isChecked    = !!selectedServices[headerItem.id];
                    card.innerHTML = washingCard(subKey, subData, headerItem, selectable, packagePrice, isChecked);
                } else {
                    // Standard: accordion sub-card
                    card.innerHTML = subCard(subKey, subData, selectable);
                }

                subcatList.appendChild(card);
            });

        // Restore expanded sub if any
        if (expandedSub) openSubAccordion(expandedSub);
    }

    // ─── Washing card template ─────────────────────────────────────────────────
    function washingCard(subKey, subData, headerItem, selectable, packagePrice, isChecked) {
        const checkedClass = isChecked
            ? 'border-secondary bg-secondary/5'
            : 'border-outline-variant bg-white hover:border-secondary';
        return `
        <div class="w-full transition-colors ${checkedClass}" id="wash-wrap-${headerItem.id}">
            <div class="w-full p-md flex items-center justify-between">
                <div class="flex items-center gap-md">
                    <input type="checkbox"
                        id="wash-cb-${headerItem.id}"
                        ${isChecked ? 'checked' : ''}
                        onchange="WalkIn.toggleWashing(${headerItem.id}, '${subKey}')"
                        class="w-5 h-5 accent-secondary cursor-pointer">
                    <label for="wash-cb-${headerItem.id}" class="font-bold uppercase cursor-pointer text-primary">
                        ${subData.name}
                    </label>
                </div>
                <div class="flex items-center gap-md">
                    <span class="font-mono font-bold text-secondary">$${packagePrice.toFixed(2)}</span>
                    <button type="button" onclick="WalkIn.toggleWashDetails('${subKey}')"
                        class="flex items-center gap-xs text-outline hover:text-primary transition-colors">
                        <span class="font-mono text-[10px] uppercase tracking-wider" id="wash-toggle-label-${subKey}">Show Details</span>
                        <span class="material-symbols-outlined text-base transition-transform" id="wash-toggle-icon-${subKey}">expand_more</span>
                    </button>
                </div>
            </div>
            <div id="wash-details-${subKey}" class="hidden border-t border-outline-variant p-md bg-surface-container-low/50">
                <p class="font-mono text-[9px] uppercase tracking-widest text-outline mb-sm font-bold">Included Package Services:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-sm">
                    ${selectable.map(item => `
                        <div class="flex items-start gap-sm">
                            <span class="material-symbols-outlined text-[#059669] text-sm mt-0.5 select-none">check_circle</span>
                            <div>
                                <p class="text-xs font-bold uppercase text-primary">${item.name}</p>
                                <p class="text-[10px] text-on-surface-variant">${item.description || ''}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>`;
    }

    // ─── Standard sub-card template ───────────────────────────────────────────
    function subCard(subKey, subData, selectable) {
        const firstIcon  = selectable[0]?.icon || 'expand_more';
        return `
        <button type="button" onclick="WalkIn.toggleSub('${subKey}')"
            class="w-full p-md flex justify-between items-center hover:bg-surface-container-low transition-colors">
            <div class="flex items-center gap-md">
                <span class="material-symbols-outlined text-secondary text-2xl">${firstIcon}</span>
                <span class="font-bold uppercase text-primary">${subData.name}</span>
            </div>
            <div class="flex items-center gap-md">
                <span class="font-mono text-xs text-outline">${selectable.length} options</span>
                <span class="material-symbols-outlined text-outline transition-transform" id="sub-icon-${subKey}">expand_more</span>
            </div>
        </button>
        <div id="sub-content-${subKey}" class="hidden border-t border-outline-variant p-md space-y-sm">
            ${selectable.map(item => serviceOptionRow(item)).join('')}
        </div>`;
    }

    // ─── Individual service option row ────────────────────────────────────────
    function serviceOptionRow(item) {
        const isChecked  = !!selectedServices[item.id];
        const borderCls  = isChecked
            ? 'border-secondary bg-secondary/5'
            : 'border-outline-variant hover:border-secondary';
        return `
        <label id="svc-label-${item.id}"
            class="flex items-start gap-md p-sm border cursor-pointer transition-colors ${borderCls}">
            <input type="checkbox"
                id="cb-${item.id}"
                ${isChecked ? 'checked' : ''}
                onchange="WalkIn.toggleService(${item.id}, this)"
                class="mt-1 accent-secondary cursor-pointer">
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <p class="font-bold text-sm uppercase text-primary">${item.name}</p>
                    <span class="font-mono text-secondary font-bold ml-2">
                        ${parseFloat(item.price) > 0 ? '$' + parseFloat(item.price).toFixed(2) : 'FREE'}
                    </span>
                </div>
                <p class="text-xs text-outline mt-1">${item.description || ''}</p>
            </div>
        </label>`;
    }

    // ─── Toggle sub accordion ─────────────────────────────────────────────────
    function toggleSub(subKey) {
        const content = $(`sub-content-${subKey}`);
        const icon    = $(`sub-icon-${subKey}`);
        if (!content) return;

        const isOpen = !content.classList.contains('hidden');

        // Close all open sub-accordions first
        if (expandedSub && expandedSub !== subKey) {
            const prevContent = $(`sub-content-${expandedSub}`);
            const prevIcon    = $(`sub-icon-${expandedSub}`);
            if (prevContent) prevContent.classList.add('hidden');
            if (prevIcon)    prevIcon.style.transform = '';
        }

        if (isOpen) {
            content.classList.add('hidden');
            icon.style.transform = '';
            expandedSub = null;
        } else {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
            expandedSub = subKey;
        }
    }

    function openSubAccordion(subKey) {
        const content = $(`sub-content-${subKey}`);
        const icon    = $(`sub-icon-${subKey}`);
        if (content) content.classList.remove('hidden');
        if (icon)    icon.style.transform = 'rotate(180deg)';
    }

    // ─── Washing details toggle ───────────────────────────────────────────────
    function toggleWashDetails(subKey) {
        const details = $(`wash-details-${subKey}`);
        const label   = $(`wash-toggle-label-${subKey}`);
        const icon    = $(`wash-toggle-icon-${subKey}`);
        if (!details) return;
        const isHidden = details.classList.toggle('hidden');
        label.textContent = isHidden ? 'Show Details' : 'Hide Details';
        icon.style.transform = isHidden ? '' : 'rotate(180deg)';
    }

    // ─── Service selection logic ───────────────────────────────────────────────
    function toggleService(id, checkbox) {
        const item = serviceIndex[id];
        if (!item) return;

        const label = $(`svc-label-${id}`);
        if (checkbox.checked) {
            selectedServices[id] = { ...item, main_category: expandedMain };
            if (label) {
                label.classList.remove('border-outline-variant', 'hover:border-secondary');
                label.classList.add('border-secondary', 'bg-secondary/5');
            }
        } else {
            delete selectedServices[id];
            if (label) {
                label.classList.remove('border-secondary', 'bg-secondary/5');
                label.classList.add('border-outline-variant', 'hover:border-secondary');
            }
        }
        updateHiddenInputs();
        updateSummary();
    }

    function toggleWashing(headerItemId, subKey) {
        const item = serviceIndex[headerItemId];
        if (!item) return;

        const cb   = $(`wash-cb-${headerItemId}`);
        const wrap = $(`wash-wrap-${headerItemId}`);

        // Deselect all other washing packages
        Object.keys(selectedServices).forEach(id => {
            if (selectedServices[id]?.main_category === 'washing') {
                delete selectedServices[id];
            }
        });

        if (cb && cb.checked) {
            selectedServices[headerItemId] = { ...item, main_category: 'washing' };
            if (wrap) { wrap.classList.remove('border-outline-variant', 'hover:border-secondary'); wrap.classList.add('border-secondary', 'bg-secondary/5'); }
        } else {
            if (wrap) { wrap.classList.remove('border-secondary', 'bg-secondary/5'); wrap.classList.add('border-outline-variant', 'hover:border-secondary'); }
        }

        // Re-render subcategories for washing so other washing checkboxes show unchecked
        renderSubcategories('washing');

        updateHiddenInputs();
        updateSummary();
    }

    // ─── Remove single service directly from Selection Summary ───────────────
    function removeService(id) {
        delete selectedServices[id];

        // Uncheck the checkbox if currently visible/rendered
        const cb = $(`cb-${id}`);
        if (cb) cb.checked = false;

        const washCb = $(`wash-cb-${id}`);
        if (washCb) washCb.checked = false;

        // Reset label styling if active
        const label = $(`svc-label-${id}`);
        if (label) {
            label.classList.remove('border-secondary', 'bg-secondary/5');
            label.classList.add('border-outline-variant', 'hover:border-secondary');
        }

        const wrap = $(`wash-wrap-${id}`);
        if (wrap) {
            wrap.classList.remove('border-secondary', 'bg-secondary/5');
            wrap.classList.add('border-outline-variant', 'hover:border-secondary');
        }

        // If in washing mode, re-render to update checkbox visuals
        if (expandedMain === 'washing') {
            renderSubcategories('washing');
        }

        updateHiddenInputs();
        updateSummary();
    }

    // ─── Hidden inputs for form submission ────────────────────────────────────
    function updateHiddenInputs() {
        hiddenInputs.innerHTML = '';
        Object.values(selectedServices).forEach(svc => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'service_ids[]';
            input.value = svc.id;
            hiddenInputs.appendChild(input);
        });
    }

    // ─── Live selection summary panel ────────────────────────────────────────
    function updateSummary() {
        const items = Object.values(selectedServices);
        const empty = $('summary-empty');
        const list  = $('summary-items');
        const total = $('summary-total');
        const amt   = $('summary-total-amount');

        if (items.length === 0) {
            empty.classList.remove('hidden');
            list.classList.add('hidden');
            total.classList.add('hidden');
            return;
        }

        empty.classList.add('hidden');
        list.classList.remove('hidden');
        total.classList.remove('hidden');

        list.innerHTML = items.map(svc => `
            <div class="flex items-center justify-between gap-sm border-b border-outline-variant/60 pb-xs last:border-0 last:pb-0">
                <div class="min-w-0">
                    <p class="font-mono text-[10px] font-bold uppercase tracking-wide text-primary truncate">${svc.name}</p>
                    <p class="font-mono text-[10px] text-outline uppercase">${svc.main_category || '—'}</p>
                </div>
                <div class="flex items-center gap-sm">
                    <span class="font-mono text-[11px] font-bold text-secondary shrink-0">$${parseFloat(svc.price || 0).toFixed(2)}</span>
                    <button type="button" onclick="WalkIn.removeService(${svc.id})"
                        class="border border-error px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest text-error hover:bg-error hover:text-white transition-colors">
                        Remove
                    </button>
                </div>
            </div>
        `).join('');

        const totalVal = items.reduce((s, svc) => s + parseFloat(svc.price || 0), 0);
        amt.textContent = '$' + totalVal.toFixed(2);
    }

    // ─── Status button toggle ─────────────────────────────────────────────────
    function setStatus(value) {
        $('status-hidden').value = value;
        document.querySelectorAll('.status-btn').forEach(btn => {
            const isActive = btn.dataset.status === value;
            const colorCls  = btn.dataset.color.split(' ');
            const activeCls = btn.dataset.activeColor.split(' ');
            if (isActive) {
                btn.classList.remove(...colorCls);
                btn.classList.add(...activeCls);
            } else {
                btn.classList.remove(...activeCls);
                btn.classList.add(...colorCls);
            }
        });
    }

    // ─── Expose to inline onclick ─────────────────────────────────────────────
    window.WalkIn = {
        toggleMain,
        collapseMain,
        toggleSub,
        toggleWashDetails,
        toggleService,
        toggleWashing,
        removeService,
        setStatus,
    };

    // ─── Init: apply default status styling & fetch catalog on load ───────────
    setStatus('{{ old('status', 'confirmed') }}');
    loadCatalog();

})();
</script>
@endpush
