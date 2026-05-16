<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('computer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('computer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('duration_minutes');
            $table->integer('extended_minutes')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['active','extended','completed','expired'])->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('computer_sessions'); }
};