<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('computer_id')->nullable()->after('duration_minutes')
                  ->constrained()->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['computer_id']);
            $table->dropColumn('computer_id');
        });
    }
};