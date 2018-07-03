<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXzPusherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xz_pusher', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('phone');
            $table->unsignedInteger('groupid');
            $table->string('openid');
            $table->unsignedInteger('isuser');
            $table->unsignedInteger('status');
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
        Schema::dropIfExists('xz_pusher');
    }
}
