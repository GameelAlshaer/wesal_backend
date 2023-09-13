<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {

            $table->increments('id',);
            $table->integer('chat_id',)->unsigned();
            $table->integer('sender_id',)->unsigned()->nullable();
            $table->integer('reciever_id',)->unsigned();
            $table->string('content')->nullable();
            $table->string('img_url')->nullable();
            $table->integer('status',);
            $table->boolean('isImg')->nullable();
            $table->boolean('isDeleted')->default(false);
            $table->integer('replyMsg')->nullable();
            $table->timestamp('created_at');
            $table->date('updated_at');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reciever_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
