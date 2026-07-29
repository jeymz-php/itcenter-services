<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Guests aren't logged in and have no service_requests row — their
            // request lives in guest_requests instead. service_request_id and
            // user_id become optional so a rating can point at either a
            // logged-in student's request OR a guest's, never both.
            $table->foreignId('service_request_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->change();

            $table->foreignId('guest_request_id')->nullable()->after('user_id')
                  ->constrained('guest_requests')->cascadeOnDelete();
            $table->unique('guest_request_id');
        });
    }

    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guest_request_id');
        });
    }
};