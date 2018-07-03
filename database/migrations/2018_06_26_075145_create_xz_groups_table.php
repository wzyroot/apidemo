<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXzGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xz_groups', function (Blueprint $table) {
            $table->increments('groupid');
            $table->string('name')->unique();       //'name'列唯一
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
        Schema::dropIfExists('xz_groups');
    }
}
