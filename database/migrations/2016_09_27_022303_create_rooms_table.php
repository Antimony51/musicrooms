<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->unsigned()->nullable();
            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->string('name')->unique();
            $table->enum('visibility', ['public', 'private']);
            $table->string('title');
            $table->string('description')->nullable();
            $table->integer('current_track_id')->unsigned()->nullable();
            $table->foreign('current_track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rooms');
    }
}
