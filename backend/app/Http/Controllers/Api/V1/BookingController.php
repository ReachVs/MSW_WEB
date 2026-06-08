<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Support\WorkshopCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $request->user()->bookings()
            ->with('user')
            ->latest('created_at')
            ->paginate(50);

        return BookingResource::collection($bookings);
    }

    public function my(Request $request): AnonymousResourceCollection
    {
        return $this->index($request);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['status'] = Booking::STATUS_PENDING;
        $data['customer_name'] = $data['customer_name'] ?? $request->user()->name;
        $data['customer_email'] = $data['customer_email'] ?? $request->user()->email;
        $data['starts_at'] = $data['starts_at'] ?? now();
        $data['starts_at'] = \Carbon\Carbon::parse($data['starts_at'])->seconds(0);
        $data['ends_at'] = $data['ends_at'] ?? \Carbon\Carbon::parse($data['starts_at'])->copy()->addHour();
        $data['selected_services'] = $data['selected_services'] ?? [];

        if ($data['starts_at']->lt(now())) {
            return response()->json([
                'message' => 'Bookings cannot be scheduled in the past.',
            ], 422);
        }

        if (! WorkshopCalendar::isSlotAvailable($data['starts_at'])) {
            return response()->json([
                'message' => 'The selected time slot is no longer available.',
            ], 422);
        }

        if (! array_key_exists('total_amount', $data)) {
            $data['total_amount'] = collect($data['selected_services'])
                ->sum(fn (array $service): float => (float) ($service['price'] ?? 0));
        }

        $booking = $request->user()->bookings()->create($data);

        BookingCreated::dispatch($booking);

        return (new BookingResource($booking->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return new BookingResource($booking->load(['mechanic', 'user']));
    }

    public function active(Request $request): AnonymousResourceCollection
    {
        $activeBookings = $request->user()->bookings()
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->with(['mechanic', 'user'])
            ->latest('created_at')
            ->get();

        return BookingResource::collection($activeBookings);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $completedBookings = $request->user()->bookings()
            ->whereIn('status', Booking::HISTORY_STATUSES)
            ->with(['mechanic', 'user'])
            ->latest('updated_at')
            ->get();

        return BookingResource::collection($completedBookings);
    }

    public function cancel(Request $request, Booking $booking): BookingResource|JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if (! $booking->canBeCancelledByCustomer()) {
            return response()->json([
                'message' => 'This booking can only be cancelled while it is pending or confirmed.',
            ], 422);
        }

        $booking->forceFill(['status' => Booking::STATUS_CANCELLED])->save();

        return new BookingResource($booking->load(['mechanic', 'user']));
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if (! in_array($booking->status, Booking::HISTORY_STATUSES, true)) {
            return response()->json([
                'message' => 'Only archived bookings can be removed from service history.',
            ], 422);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Service history record removed successfully.',
        ]);
    }

    public function userVehicles(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $vehicles = $user->bookings()
                         ->whereNotNull('notes')
                         ->select('notes')
                         ->distinct()
                         ->pluck('notes');

        return response()->json(['data' => $vehicles]);
    }
}
