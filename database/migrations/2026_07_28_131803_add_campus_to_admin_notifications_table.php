<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->string('campus')->nullable()->after('notifiable_type');
        });
    }

    public function down()
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropColumn('campus');
        });
    }
};