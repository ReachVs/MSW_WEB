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
