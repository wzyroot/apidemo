<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHnSymptomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hn_symptoms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('diseaseid');
            $table->test('symptoms');       //'name'列唯一diseasename
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hn_symptoms');
    }
}
