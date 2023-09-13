<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoUserQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('info_user_questions', function (Blueprint $table) {

		$table->integer('question_id',)->unsigned();
		$table->integer('user_id',)->unsigned();
        $table->timestamp('created_at');
        $table->date('updated_at');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');


        });
    }

    public function down()
    {
        Schema::dropIfExists('info_user_questions');
    }
}
