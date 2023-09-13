<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {

		$table->increments('id',);
		$table->integer('sender_id',)->unsigned();
		$table->integer('reciever_id',)->unsigned();
		$table->integer('status',);
        $table->timestamp('created_at');
        $table->date('updated_at');
        $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('reciever_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
}
