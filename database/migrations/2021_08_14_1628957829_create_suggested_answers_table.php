<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestedAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('suggested_answers', function (Blueprint $table) {

		$table->increments('id',);
		$table->integer('question_id',)->unsigned();
		$table->string('answer');
        $table->timestamp('created_at');
        $table->date('updated_at');
       $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('suggested_answers');
    }
}
