<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

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
            $table->string('username')->unique();
            $table->string('email');
            $table->string('password', 60)->nullable()->default(null);
            $table->string('surname', 255);
            $table->string('forenames', 255);
            $table->boolean('is_convenor')->default(false);
            $table->boolean('is_student')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->datetime('last_login')->nullable()->default(null);
            $table->text('bio')->nullable();
            $table->string('cv_file')->nullable();
            $table->string('institution')->default('UoG');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
