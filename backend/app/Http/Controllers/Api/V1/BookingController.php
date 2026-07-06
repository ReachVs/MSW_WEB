<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Service;
use App\Support\WorkshopCalendar;
use Carbon\Carbon;
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
        $selectedServices = $this->resolveSelectedServices($data);

        if ($selectedServices === []) {
            return response()->json([
                'message' => 'Please select at least one valid service.',
            ], 422);
        }

        $data['status'] = Booking::STATUS_PENDING;
        $data['customer_name'] = $data['customer_name'] ?? $request->user()->name;
        $data['customer_email'] = $data['customer_email'] ?? $request->user()->email;
        $data['customer_phone'] = $data['customer_phone'] ?? $request->user()->phone;
        $data['starts_at'] = $data['starts_at'] ?? now();
        $data['starts_at'] = Carbon::parse($data['starts_at'])->seconds(0);
        $data['ends_at'] = $data['ends_at'] ?? Carbon::parse($data['starts_at'])->copy()->addHour();
        $data['selected_services'] = $selectedServices;
        $data['service_id'] = $selectedServices[0]['id'];
        $data['service_name'] = collect($selectedServices)
            ->pluck('name')
            ->implode(', ');
        $data['total_amount'] = collect($selectedServices)
            ->sum(fn (array $service): float => (float) $service['price']);

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

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $vehicles = $user->bookings()
            ->whereNotNull('notes')
            ->select('notes')
            ->distinct()
            ->pluck('notes');

        return response()->json(['data' => $vehicles]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function resolveSelectedServices(array $data): array
    {
        $requestedIds = collect($data['selected_services'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id);

        if ($requestedIds->isEmpty() && isset($data['service_id'])) {
            $requestedIds->push((int) $data['service_id']);
        }

        $requestedIds = $requestedIds
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            return [];
        }

        $servicesById = Service::query()
            ->active()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        if ($servicesById->count() !== $requestedIds->count()) {
            return [];
        }

        $hasUnsupportedService = $requestedIds->contains(function (int $serviceId) use ($servicesById): bool {
            /** @var Service $service */
            $service = $servicesById->get($serviceId);

            return $service->selection_mode !== 1
                && ! ($service->main_category === 'washing' && $service->selection_mode === 0);
        });

        if ($hasUnsupportedService) {
            return [];
        }

        return $requestedIds->map(function (int $serviceId) use ($servicesById): array {
            /** @var Service $service */
            $service = $servicesById->get($serviceId);
            $price = (float) ($service->price ?? 0);

            if ($service->main_category === 'washing' && $service->selection_mode === 0) {
                $price = (float) Service::query()
                    ->active()
                    ->where('main_category', 'washing')
                    ->where('sub_category', $service->sub_category)
                    ->where('selection_mode', 1)
                    ->sum('price');
            }

            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $price,
                'category' => $service->category,
                'main_category' => $service->main_category,
                'sub_category' => $service->sub_category,
                'icon' => $service->icon,
                'image' => $service->image,
            ];
        })->all();
    }
}
