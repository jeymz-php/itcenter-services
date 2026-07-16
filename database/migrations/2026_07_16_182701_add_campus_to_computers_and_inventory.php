<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->string('campus')->nullable()->after('name');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('campus')->nullable()->after('category');
        });

        $campuses = array_keys(config('campuses'));
        $firstCampus = $campuses[0] ?? 'main';

        // Computers are physical units — we can't know which campus each existing
        // one actually belongs to, so they all get assigned to the first configured
        // campus. Reassign them individually afterwards in Manage Computers.
        DB::table('computers')->whereNull('campus')->update(['campus' => $firstCampus]);

        // Inventory (paper stock, PC duration options) is different: rather than
        // guessing and leaving 3 campuses with zero stock, every existing item is
        // cloned into each OTHER campus with the same starting counts, so nothing
        // silently disappears. Adjust actual per-campus stock afterwards.
        $items = DB::table('inventory_items')->whereNull('campus')->get();
        foreach ($items as $item) {
            DB::table('inventory_items')->where('id', $item->id)->update(['campus' => $firstCampus]);

            foreach (array_slice($campuses, 1) as $campus) {
                DB::table('inventory_items')->insert([
                    'category'   => $item->category,
                    'campus'     => $campus,
                    'name'       => $item->name,
                    'value'      => $item->value,
                    'price'      => $item->price,
                    'stock'      => $item->stock,
                    'is_active'  => $item->is_active,
                    'sort_order' => $item->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('computers', function (Blueprint $table) {
            $table->dropColumn('campus');
        });
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('campus');
        });
    }
};