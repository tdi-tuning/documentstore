<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docstore_files', function(Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->integer('revision_id')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('docstore_files');
    }
}
