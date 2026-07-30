<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'category',
        'campus',
        'name',
        'value',
        'price',
        'stock',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // $campus is required in practice (inventory is per-campus, not shared) but left
    // nullable-tolerant here so a bad/missing campus just returns an empty list
    // instead of throwing.
    public static function paperSizes(?string $campus): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', 'paper_size')
                     ->where('campus', $campus)
                     ->where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();
    }

    public static function pcDurations(?string $campus): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', 'pc_duration')
                     ->where('campus', $campus)
                     ->where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();
    }

    public function stockLogs() {
        return $this->hasMany(InventoryStockLog::class)->latest();
    }
}