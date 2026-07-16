<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->enum('closed_by', ['user', 'admin'])->nullable();
            $table->unsignedBigInteger('closed_by_admin_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'closed_at']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('closed_by_admin_id')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_session_id')->nullable()->after('user_id');
            $table->foreign('chat_session_id')->references('id')->on('chat_sessions')->onDelete('cascade');
        });

        // Backfill: every existing message gets wrapped in one open session per user,
        // so nothing already sent disappears when this ships.
        $users = \Illuminate\Support\Facades\DB::table('messages')->select('user_id')->distinct()->pluck('user_id');
        foreach ($users as $userId) {
            $sessionId = \Illuminate\Support\Facades\DB::table('chat_sessions')->insertGetId([
                'user_id'    => $userId,
                'opened_at'  => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \Illuminate\Support\Facades\DB::table('messages')->where('user_id', $userId)->update(['chat_session_id' => $sessionId]);
        }
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['chat_session_id']);
            $table->dropColumn('chat_session_id');
        });
        Schema::dropIfExists('chat_sessions');
    }
};