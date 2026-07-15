<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // The user this conversation thread belongs to (every thread is User <-> IT Center Admins)
            $table->unsignedBigInteger('user_id');

            // Optional link to a specific service request (printing / photocopy / research follow-up)
            $table->unsignedBigInteger('service_request_id')->nullable();

            // Who sent this particular message
            $table->enum('sender_type', ['user', 'admin']);

            // Which admin sent it (null when sender_type = user)
            $table->unsignedBigInteger('sender_admin_id')->nullable();

            $table->text('body');

            $table->boolean('is_read_by_user')->default(false);
            $table->boolean('is_read_by_admin')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('set null');
            $table->foreign('sender_admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};