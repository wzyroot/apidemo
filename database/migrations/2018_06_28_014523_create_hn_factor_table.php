<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHnFactorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hn_factor', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('diseaseid');
            $table->test('factor');       //'name'列唯一diseasename
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hn_factor');
    }
}
