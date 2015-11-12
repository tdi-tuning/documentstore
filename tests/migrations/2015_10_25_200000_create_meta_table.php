<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docstore_meta', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
        });
    }
    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('docstore_meta');
    }
}
