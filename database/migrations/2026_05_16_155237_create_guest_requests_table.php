<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('guest_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->enum('role', ['student','faculty_staff','visitor']);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('id_number')->nullable(); // student/faculty number
            $table->string('campus');
            $table->enum('service_type', ['printing','photocopy']);
            $table->enum('status', ['pending','approved','processing','completed','rejected','cancelled'])
                  ->default('pending');

            // Printing
            $table->string('paper_size')->nullable();
            $table->integer('copies')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->enum('print_type', ['black_white','colored'])->nullable();
            $table->text('purpose')->nullable();

            // Admin
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('guest_requests'); }
};