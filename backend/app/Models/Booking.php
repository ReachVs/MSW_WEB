<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REPAIR = 'repair';

    public const STATUS_WAITING_PART = 'waiting_part';

    public const STATUS_READY_PICKUP = 'ready_pickup';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_REPAIR,
        self::STATUS_WAITING_PART,
        self::STATUS_READY_PICKUP,
    ];

    public const HISTORY_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const CUSTOMER_CANCELLABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
    ];

    protected $fillable = [
        'user_id',
        'mechanic_id',
        'service_id',
        'service_name',
        'selected_services',
        'total_amount',
        'bike_name',
        'model',
        'plate_number',
        'engine_capacity',
        'customer_name',
        'customer_email',
        'starts_at',
        'ends_at',
        'status',
        'notes',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'selected_services' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_REPAIR,
            self::STATUS_WAITING_PART,
            self::STATUS_READY_PICKUP,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public function getCategoryKey(): ?string
    {
        $selectedServices = $this->selected_services ?? [];
        if (! is_array($selectedServices)) {
            $selectedServices = [];
        }

        // 1. Try explicit category
        foreach ($selectedServices as $service) {
            $category = $service['category'] ?? $service['main_category'] ?? null;
            if ($category) {
                return $category;
            }
        }

        // 2. Fallback to matching keywords in names/descriptions
        $texts = [];
        foreach ($selectedServices as $service) {
            $texts[] = ($service['name'] ?? '').' '.($service['description'] ?? '');
        }
        if (empty($texts) && $this->service_name) {
            $texts[] = $this->service_name.' '.($this->notes ?? '');
        }

        $allNames = strtolower(implode(' ', $texts));

        if (str_contains($allNames, 'wash') || str_contains($allNames, 'clean') || str_contains($allNames, 'detail')) {
            return 'washing';
        }
        if (
            str_contains($allNames, 'engine') ||
            str_contains($allNames, 'oil') ||
            str_contains($allNames, 'checkup') ||
            str_contains($allNames, 'spark') ||
            str_contains($allNames, 'fluid') ||
            str_contains($allNames, 'filter')
        ) {
            return 'engine_checkup';
        }
        if (
            str_contains($allNames, 'tun') ||
            str_contains($allNames, 'performance') ||
            str_contains($allNames, 'speed') ||
            str_contains($allNames, 'ecu') ||
            str_contains($allNames, 'dyno') ||
            str_contains($allNames, '1000cc') ||
            str_contains($allNames, '600cc') ||
            str_contains($allNames, '200cc') ||
            str_contains($allNames, '400cc') ||
            str_contains($allNames, 'class') ||
            str_contains($allNames, 'above')
        ) {
            return 'tuning';
        }
        if (
            str_contains($allNames, 'mainten') ||
            str_contains($allNames, 'repair') ||
            str_contains($allNames, 'inspect') ||
            str_contains($allNames, 'brake') ||
            str_contains($allNames, 'tire')
        ) {
            return 'maintenance';
        }

        return null;
    }

    public function getCategoryDetails(): ?array
    {
        $key = $this->getCategoryKey();
        if (! $key) {
            return null;
        }

        $details = [
            'maintenance' => [
                'label' => 'Maintenance Services',
                'icon' => 'build',
                'image' => '/mechanic-chain.png',
            ],
            'washing' => [
                'label' => 'Washing Services',
                'icon' => 'wash',
                'image' => '/motorcycle-wash.png',
            ],
            'engine_checkup' => [
                'label' => 'Engine Check Up',
                'icon' => 'monitor_heart',
                'image' => '/mechanic-diagnostic.png',
            ],
            'tuning' => [
                'label' => 'Tuning Performance',
                'icon' => 'speed',
                'image' => '/dyno-tuning.jpg',
            ],
        ];

        return $details[$key] ?? null;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, self::CUSTOMER_CANCELLABLE_STATUSES, true);
    }

    /**
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_REPAIR, self::STATUS_CANCELLED],
            self::STATUS_REPAIR => [self::STATUS_WAITING_PART, self::STATUS_READY_PICKUP, self::STATUS_CANCELLED],
            self::STATUS_WAITING_PART => [self::STATUS_REPAIR, self::STATUS_CANCELLED],
            self::STATUS_READY_PICKUP => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            self::STATUS_COMPLETED, self::STATUS_CANCELLED => [],
            default => throw new InvalidArgumentException("Unsupported booking status [{$this->status}]."),
        };
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return in_array($nextStatus, $this->allowedNextStatuses(), true);
    }
}
