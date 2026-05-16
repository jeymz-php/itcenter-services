<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Paper sizes
        $papers = [
            ['name' => 'A4 (210 × 297 mm)',   'value' => 'a4',     'price' => 5.00, 'stock' => 500],
            ['name' => 'Letter (8.5 × 11 in)', 'value' => 'letter', 'price' => 5.00, 'stock' => 500],
            ['name' => 'Legal (8.5 × 14 in)',  'value' => 'legal',  'price' => 6.00, 'stock' => 300],
            ['name' => 'Short (8.5 × 11 in)',  'value' => 'short',  'price' => 5.00, 'stock' => 400],
            ['name' => 'Long (8.5 × 13 in)',   'value' => 'long',   'price' => 6.00, 'stock' => 300],
        ];

        foreach ($papers as $i => $p) {
            InventoryItem::updateOrCreate(
                ['category' => 'paper_size', 'value' => $p['value']],
                array_merge($p, ['category' => 'paper_size', 'sort_order' => $i])
            );
        }

        // PC durations
        $durations = [
            ['name' => '15 Minutes', 'value' => '15', 'price' => 10.00, 'stock' => 999],
            ['name' => '30 Minutes', 'value' => '30', 'price' => 20.00, 'stock' => 999],
            ['name' => '45 Minutes', 'value' => '45', 'price' => 30.00, 'stock' => 999],
            ['name' => '60 Minutes', 'value' => '60', 'price' => 40.00, 'stock' => 999],
        ];

        foreach ($durations as $i => $d) {
            InventoryItem::updateOrCreate(
                ['category' => 'pc_duration', 'value' => $d['value']],
                array_merge($d, ['category' => 'pc_duration', 'sort_order' => $i])
            );
        }
    }
}