<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminTable extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {

		$table->increments('id',);
		$table->string('username');
		$table->string('password');
		$table->boolean('super_admin');
        $table->timestamp('created_at');
        $table->date('updated_at');

        });
    }

    public function down()
    {
        Schema::dropIfExists('admin');
    }
}
