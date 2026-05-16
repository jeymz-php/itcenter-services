<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->after('last_name');
            $table->string('profile_picture')->nullable()->after('email');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->after('user_type');
            $table->timestamp('verified_at')->nullable()->after('status');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email','profile_picture','status','verified_at']);
        });
    }
};