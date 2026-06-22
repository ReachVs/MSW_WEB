<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'category_id',
        'stock_qty',
        'minimum_stock',
        'status',
        'unit_price_cents',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'stock_qty' => 'integer',
        'minimum_stock' => 'integer',
        'unit_price_cents' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'part_id')->latest();
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_qty == 0) {
            return 'Out of Stock';
        }
        if ($this->stock_qty <= $this->minimum_stock) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function getUnitPriceAttribute(): float
    {
        return $this->unit_price_cents / 100;
    }
}
