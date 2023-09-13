<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavsTable extends Migration
{
    public function up()
    {
        Schema::create('favs', function (Blueprint $table) {

            $table->increments('id',);
            $table->integer('user_1',)->unsigned();
            $table->integer('user_2',)->unsigned();
            $table->string('name');
            $table->float('age');
            $table->string('user2_image')->nullable();
            $table->timestamp('created_at');
            $table->date('updated_at');
            $table->foreign('user_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_2')->references('id')->on('users')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('favs');
    }
}
