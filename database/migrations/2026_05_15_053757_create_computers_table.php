<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. PC-01
            $table->string('specs')->nullable();
            $table->enum('status', ['available','in_use','deactivated'])->default('available');
            $table->text('deactivation_note')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('computers'); }
};