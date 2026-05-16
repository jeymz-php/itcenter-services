<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Computer;

class ComputerSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Computer::updateOrCreate(
                ['name' => 'PC-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'specs'      => 'Intel Core i5, 8GB RAM, 256GB SSD',
                    'status'     => 'available',
                    'sort_order' => $i,
                ]
            );
        }
    }
}