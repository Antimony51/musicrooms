<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['file', 'youtube', 'soundcloud']);
            $table->string('uri', 40);
            $table->text('link');
            $table->text('title')->nullable();
            $table->text('artist')->nullable();
            $table->text('album')->nullable();
            $table->double('duration');
            $table->timestamps();
            $table->unique(['type', 'uri']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tracks');
    }
}
