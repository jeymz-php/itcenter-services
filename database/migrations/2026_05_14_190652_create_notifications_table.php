<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // new_registration, account_request, new_service_request, etc.
            $table->string('title');
            $table->text('message');
            $table->morphs('notifiable'); // user or admin
            $table->boolean('is_read')->default(false);
            $table->string('action_url')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('admin_notifications');
    }
};