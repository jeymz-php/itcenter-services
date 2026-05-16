<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Add duration_minutes column
        Schema::table('guest_requests', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable()->after('print_type');
        });

        // Fix service_type ENUM using raw SQL (avoids Doctrine DBAL enum issue)
        DB::statement("ALTER TABLE guest_requests MODIFY COLUMN service_type ENUM('printing','photocopy','research') NOT NULL");
    }

    public function down(): void {
        Schema::table('guest_requests', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
        DB::statement("ALTER TABLE guest_requests MODIFY COLUMN service_type ENUM('printing','photocopy') NOT NULL");
    }
};