<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoUsersTable extends Migration
{
    public function up()
    {
        Schema::create('info_users', function (Blueprint $table) {

		$table->increments('id');
		$table->integer('user_id')->unsigned();
        $table->integer('question_id',)->unsigned()->nullable();
		$table->integer('answer_id',)->unsigned()->nullable();
		$table->string('answer');
        $table->timestamp('created_at');
        $table->date('updated_at');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        $table->foreign('answer_id')->references('id')->on('suggested_answers')->onDelete('cascade');
        $table->boolean('hidden')->default(0);

        });

        // Schema::table('info_users', function($table)
        // {
        //     $table->foreign('user_id')->references('id')->on('users');
        // });
    }

    public function down()
    {
        Schema::dropIfExists('info_user');
    }
}
