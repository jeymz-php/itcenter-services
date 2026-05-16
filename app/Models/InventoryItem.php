<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'category',
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

    public static function paperSizes(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', 'paper_size')
                     ->where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();
    }

    public static function pcDurations(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', 'pc_duration')
                     ->where('is_active', true)
                     ->orderBy('sort_order')
                     ->get();
    }
}