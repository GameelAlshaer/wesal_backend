<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

		    $table->increments('id');
		    $table->string('name');
		    $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
		    $table->string('password');
		    $table->string('phone');
		    $table->date('birth_day');
            $table->integer('age');
		    $table->string('gender');
            $table->boolean('answered')->nullable();
		    $table->string('image')->nullable();
		    $table->integer('reports')->nullable();
		    $table->boolean('ban')->nullable();
		    $table->integer('ban_count')->nullable();
		    $table->boolean('certified')->nullable();
		    $table->boolean('VIP')->nullable();
		    $table->timestamp('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->rememberToken();
            $table->string('id_number')->nullable();
            $table->boolean('online')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
