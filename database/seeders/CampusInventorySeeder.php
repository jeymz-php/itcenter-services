<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;

class CampusInventorySeeder extends Seeder
{
    public function run(): void
    {
        $campuses = array_keys(config('campuses'));

        foreach ($campuses as $campus) {

            // Short bond paper — 500 sheets per campus
            InventoryItem::updateOrCreate(
                ['category' => 'paper_size', 'campus' => $campus, 'value' => 'short'],
                [
                    'name'       => 'Short (8.5 × 11 in)',
                    'price'      => 5.00,
                    'stock'      => 500,
                    'is_active'  => true,
                    'sort_order' => 1,
                ]
            );

            // PC durations — 15 / 30 / 45 / 60 minutes per campus
            $durations = [
                ['value' => '15', 'name' => '15 Minutes', 'price' => 10.00, 'sort_order' => 1],
                ['value' => '30', 'name' => '30 Minutes', 'price' => 20.00, 'sort_order' => 2],
                ['value' => '45', 'name' => '45 Minutes', 'price' => 30.00, 'sort_order' => 3],
                ['value' => '60', 'name' => '60 Minutes', 'price' => 40.00, 'sort_order' => 4],
            ];

            foreach ($durations as $d) {
                InventoryItem::updateOrCreate(
                    ['category' => 'pc_duration', 'campus' => $campus, 'value' => $d['value']],
                    [
                        'name'       => $d['name'],
                        'price'      => $d['price'],
                        'stock'      => 999, // durations aren't stock-limited like paper
                        'is_active'  => true,
                        'sort_order' => $d['sort_order'],
                    ]
                );
            }
        }
    }
}