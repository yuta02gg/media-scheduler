//2024_10_30_create_user_media_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMediaTable extends Migration
{
    public function up()
    {
        Schema::create('user_media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('reminder_time')->nullable();
            $table->tinyInteger('notification_type')->nullable();
            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');

            // ユニーク制約
            $table->unique(['user_id', 'media_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_media');
    }
}
