<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'category',
        'stock_pct',
        'unit_price_cents',
    ];

    protected $casts = [
        'stock_pct' => 'integer',
        'unit_price_cents' => 'integer',
    ];
}
