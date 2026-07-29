<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedTinyInteger('stars');
            $table->text('comment')->nullable();
            $table->text('suggestions')->nullable();
            $table->timestamps();

            $table->unique('service_request_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};