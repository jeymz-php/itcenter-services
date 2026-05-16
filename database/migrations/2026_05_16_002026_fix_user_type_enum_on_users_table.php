<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('student','faculty_staff') NOT NULL");
    }
    public function down(): void {
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('student','faculty','staff') NOT NULL");
    }
};