<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingManagementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->ensureAdmin($request);

        $bookings = Booking::query()
            ->with(['mechanic', 'user'])
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search').'%';

                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery
                        ->where('service_name', 'like', $search)
                        ->orWhere('bike_name', 'like', $search)
                        ->orWhere('model', 'like', $search)
                        ->orWhere('plate_number', 'like', $search)
                        ->orWhere('customer_name', 'like', $search)
                        ->orWhere('customer_email', 'like', $search);
                });
            })
            ->latest('created_at')
            ->paginate(50);

        return BookingResource::collection($bookings);
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        $this->ensureAdmin($request);

        return new BookingResource($booking->load(['mechanic', 'user']));
    }

    public function updateStatus(
        UpdateBookingStatusRequest $request,
        Booking $booking
    ): BookingResource|JsonResponse {
        $this->ensureAdmin($request);

        $nextStatus = $request->validated('status');

        if (! $booking->canTransitionTo($nextStatus)) {
            return response()->json([
                'message' => "Invalid status transition from [{$booking->status}] to [{$nextStatus}].",
                'allowed_next_statuses' => $booking->allowedNextStatuses(),
            ], 422);
        }

        $booking->forceFill(['status' => $nextStatus])->save();

        return new BookingResource($booking->load(['mechanic', 'user']));
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $this->ensureAdmin($request);

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully.']);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403, 'Admin access is required.');
    }
}
