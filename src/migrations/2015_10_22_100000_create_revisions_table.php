<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docstore_revisions', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id');
            $table->string('rev');
            $table->enum('type', ['C', 'U', 'D']);
            $table->integer('meta_id')->nullable();
            $table->timestamps();
            $table->unique(['file_id', 'rev']);
        });
    }
    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('docstore_revisions');
    }
}
