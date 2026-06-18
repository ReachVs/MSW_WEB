@php
    $mechanicRouteName = $mechanicRouteName ?? 'admin.bookings.mechanic';
    $mechanicSelectClass = $mechanicSelectClass ?? 'flex-1 border border-outline-variant bg-white px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest';
    $mechanicButtonClass = $mechanicButtonClass ?? 'border border-outline-variant px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest hover:bg-surface-container-high transition-colors';
@endphp

@if(($canAssignMechanic ?? true) && isset($mechanics))
    <form method="POST" action="{{ route($mechanicRouteName, $booking) }}" class="flex gap-sm">
        @csrf
        @method('PATCH')
        <select name="mechanic_id" class="{{ $mechanicSelectClass }}">
            <option value="">Unassigned</option>
            @foreach($mechanics as $mechanicOption)
                <option value="{{ $mechanicOption->id }}" @selected($booking->mechanic_id === $mechanicOption->id)>
                    {{ $mechanicOption->name }}@if($mechanicOption->specialization) • {{ $mechanicOption->specialization }}@endif
                </option>
            @endforeach
        </select>
        <button class="{{ $mechanicButtonClass }}">
            Assign Mechanic
        </button>
    </form>
@endif
