<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Computer;

class FixComputerCampusSeeder extends Seeder
{
    // Maps a substring found in the PC's existing name to the correct
    // config/campuses.php key. Adjust this list if "South" isn't actually
    // Main Campus — anything that doesn't match stays untouched and gets
    // listed as skipped so you can fix it manually in Manage Computers.
    private array $nameToCampus = [
        'south'         => 'main',           // "PC 01 - South" etc. -> UCC Main Campus
        'congressional' => 'congressional',
        'camarin'       => 'camarin',
        'bagong silang' => 'bagong_silang',
        'bagong_silang' => 'bagong_silang',
    ];

    public function run(): void
    {
        $computers = Computer::all();
        $updated = 0;
        $skipped = [];

        foreach ($computers as $pc) {
            $nameLower = strtolower($pc->name);
            $matchedCampus = null;

            foreach ($this->nameToCampus as $needle => $campus) {
                if (str_contains($nameLower, $needle)) {
                    $matchedCampus = $campus;
                    break;
                }
            }

            if ($matchedCampus) {
                $pc->update(['campus' => $matchedCampus]);
                $updated++;
            } else {
                $skipped[] = $pc->name;
            }
        }

        $this->command?->info("Updated campus for {$updated} computer(s).");
        if ($skipped) {
            $this->command?->warn('Could not determine campus for: ' . implode(', ', $skipped) . ' — left unchanged, fix manually in Manage Computers.');
        }
    }
}