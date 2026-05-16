<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // e.g. #000001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('service_type', ['printing','photocopy','research']);
            $table->enum('status', ['pending','approved','processing','completed','rejected','cancelled'])
                  ->default('pending');

            // Printing & Photocopy shared
            $table->string('paper_size')->nullable();
            $table->integer('copies')->nullable();
            $table->text('purpose')->nullable();

            // Printing specific
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->enum('print_type', ['black_white','colored'])->nullable();
            $table->enum('print_sides', ['single','double'])->nullable();

            // Photocopy specific
            $table->enum('document_type', ['id','document','book','other'])->nullable();

            // Research specific
            $table->integer('duration_minutes')->nullable();

            // Admin
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->decimal('total_price', 8, 2)->default(0);

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('service_requests'); }
};