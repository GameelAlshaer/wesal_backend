<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCertificationsTable extends Migration
{
    public function up()
    {
        Schema::create('user_certifications', function (Blueprint $table) {

		$table->increments('id',);
		$table->integer('user_id',)->unsigned();
		$table->string('image');
        $table->timestamp('created_at');
        $table->date('updated_at');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_certification');
    }
}
