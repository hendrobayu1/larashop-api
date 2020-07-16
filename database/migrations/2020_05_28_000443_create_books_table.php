<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('title',500);
            $table->string('slug',500)->unique();
            $table->text('description')->nullable();
            $table->string('author',500)->nullable();
            $table->string('publisher',500)->nullable();
            $table->string('cover',500)->nullable();
            $table->float('price')->unsigned()->default(0);
            $table->float('weight')->unsigned()->default(0);
            $table->integer('views')->unsigned()->default(0);
            $table->integer('stock')->unsigned()->default(0);
            $table->string('status',20)->default('PUBLISH');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by',)->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
