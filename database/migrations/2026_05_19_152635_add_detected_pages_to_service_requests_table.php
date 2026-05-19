<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->integer('detected_pages')->nullable()->after('file_name');
        });
    }
    public function down(): void {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('detected_pages');
        });
    }
};