<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageImagesTable extends Migration
{
    public function up()
    {
        Schema::create('message_images', function (Blueprint $table) {

		$table->increments('id',);
		$table->integer('message_id',)->unsigned();
		$table->string('image');
        $table->timestamp('created_at');
        $table->date('updated_at');
        $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');


        });
    }

    public function down()
    {
        Schema::dropIfExists('message_image');
    }
}
