<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallerDance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caller_dance', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->primary('id');
            $table->unsignedBigInteger('caller_id');
            $table->unsignedBigInteger('dance_id');
            $table->date('date_of');

            $table->unique(['caller_id','dance_id', 'date_of']);

            $table->foreign('caller_id')->references('id')->on('callers');
            $table->foreign('dance_id')->references('id')->on('dances');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caller_dance', function (Blueprint $table) {
            //
        });
    }
}
