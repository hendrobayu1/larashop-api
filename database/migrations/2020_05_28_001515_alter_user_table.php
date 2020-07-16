<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('roles')->nullable();
            $table->text('address')->nullable();
            $table->integer('city')->nullable();
            $table->integer('province_id')->nullable();
            $table->string('phone',30)->nullable();
            $table->string('avatar',500)->nullable();
            $table->string('status',20)->default('ACTIVE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
            $table->dropColumn('address');
            $table->dropColumn('city');
            $table->dropColumn('province_id');
            $table->dropColumn('phone');
            $table->dropColumn('avatar');
            $table->dropColumn('status');
        });
    }
}