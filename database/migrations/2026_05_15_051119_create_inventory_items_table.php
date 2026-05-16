<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // paper_size, pc_duration
            $table->string('name');     // e.g. "A4", "Letter", "Legal", "15 minutes"
            $table->string('value');    // e.g. "a4", "letter", "15"
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inventory_items'); }
};