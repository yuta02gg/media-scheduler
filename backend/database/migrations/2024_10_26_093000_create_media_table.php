// 2024_10_26_093000_create_media_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable(false);
            $table->string('media_type', 10)->nullable(false); // 'movie' または 'tv'
            $table->date('release_date')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}
