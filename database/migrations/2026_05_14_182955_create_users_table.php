<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('id_number', 20)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('campus');
            $table->enum('user_type', ['student', 'faculty', 'staff']);
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id')->unique();
            $table->string('email')->unique();
            $table->string('campus');
            $table->string('password');
            $table->enum('role', ['admin', 'super_admin'])->default('admin');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
        Schema::dropIfExists('admins');
    }
};