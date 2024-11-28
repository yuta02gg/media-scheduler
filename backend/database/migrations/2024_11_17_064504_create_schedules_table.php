<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
      Schema::create('schedules', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('media_id')->nullable();
        $table->string('title');
        $table->date('date');
        $table->timestamps();

        // 外部キー制約
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
      });
    }

};
