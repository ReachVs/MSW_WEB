<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $bookings = Booking::query()
            ->with('user')
            ->orderBy('starts_at')
            ->paginate(50);

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $request->user()->bookings()->create([
            ...$request->validated(),
            'status' => 'confirmed',
        ]);

        BookingCreated::dispatch($booking);

        return (new BookingResource($booking->load('user')))
            ->response()
            ->setStatusCode(201);
    }
}
