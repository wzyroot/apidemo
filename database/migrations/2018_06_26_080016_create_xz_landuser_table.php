<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXzLanduserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xz_landuser', function (Blueprint $table) {
            $table->increments('userid');
            $table->string('username');
            $table->string('phone');
            $table->enum('sex', ['男', '女']);
            $table->unsignedInteger('typeid');
            $table->string('manageid');
            $table->string('development');
            $table->string('province');
            $table->string('city');
            $table->string('area');
            $table->string('address');
            $table->unsignedInteger('belongid');
            $table->string('viewuser');
            $table->unsignedInteger('groupid');
            $table->text('content');
            $table->timestamp('createtime')->nullable();
            $table->timestamp('updatetime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xz_landuser');
    }
}
