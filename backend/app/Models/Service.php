<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'category',
        'icon',
        'image',
        'is_active',
        'sort_order',
        'main_category',
        'sub_category',
        'parent_id',
        'selection_mode',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'parent_id' => 'integer',
            'selection_mode' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByMainCategory($query, $mainCategory)
    {
        return $query->where('main_category', $mainCategory);
    }

    public function children()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSelectable($query)
    {
        return $query->where('selection_mode', 1);
    }
}
