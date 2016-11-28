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
            $table->string('name', 24)->unique();
            $table->enum('visibility', ['public', 'private']);
            $table->string('title', 24);
            $table->text('description')->nullable();
            $table->integer('user_limit')->default(0);
            $table->integer('user_queue_limit')->default(5);
            $table->integer('user_count')->default(0);
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
