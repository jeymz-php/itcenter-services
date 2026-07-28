<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Computer;
use App\Models\InventoryItem;

class CampusFullSetupSeeder extends Seeder
{
    // Maps each config/campuses.php key to the short label used in PC names
    // (e.g. "PC 01 - SOUTH"). "main" displays as "South" per the existing
    // convention already established in FixComputerCampusSeeder.
    private array $campusLabels = [
        'main'          => 'SOUTH',
        'congressional' => 'CONGRESS',
        'camarin'       => 'CAMARIN',
        'bagong_silang' => 'BAGONG SILANG',
    ];

    public function run(): void
    {
        $campuses = array_keys(config('campuses'));

        foreach ($campuses as $campus) {
            $label = $this->campusLabels[$campus] ?? strtoupper($campus);

            // ── 10 COMPUTERS PER CAMPUS ──
            for ($i = 1; $i <= 10; $i++) {
                $number = str_pad($i, 2, '0', STR_PAD_LEFT);
                Computer::updateOrCreate(
                    ['name' => "PC {$number} - {$label}", 'campus' => $campus],
                    [
                        'specs'      => 'Intel Core i5, 8GB RAM, 256GB SSD',
                        'status'     => 'available',
                        'sort_order' => $i,
                    ]
                );
            }

            // ── PC DURATIONS: 15 / 30 / 45 / 60 MINUTES ──
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

            // ── PAPER SIZES: SHORT (8.5 × 11) AND LONG (8.5 × 13) ──
            $papers = [
                ['value' => 'short', 'name' => 'Short (8.5 × 11 in)', 'price' => 5.00, 'stock' => 500, 'sort_order' => 1],
                ['value' => 'long',  'name' => 'Long (8.5 × 13 in)',  'price' => 6.00, 'stock' => 500, 'sort_order' => 2],
            ];
            foreach ($papers as $p) {
                InventoryItem::updateOrCreate(
                    ['category' => 'paper_size', 'campus' => $campus, 'value' => $p['value']],
                    [
                        'name'       => $p['name'],
                        'price'      => $p['price'],
                        'stock'      => $p['stock'],
                        'is_active'  => true,
                        'sort_order' => $p['sort_order'],
                    ]
                );
            }

            $this->command?->info("Seeded {$campus}: 10 PCs, 4 durations, 2 paper sizes.");
        }
    }
}