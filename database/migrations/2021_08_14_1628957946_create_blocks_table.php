<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlocksTable extends Migration
{
    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {

            $table->increments('id',);
            $table->integer('blocker_id',)->unsigned();
            $table->string('name');
            $table->float('age');
            $table->integer('blocked_id',)->unsigned();
            $table->string('blocked_image')->nullable();
            $table->timestamp('created_at');
            $table->date('updated_at');
            $table->foreign('blocker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('blocks');
    }
}
