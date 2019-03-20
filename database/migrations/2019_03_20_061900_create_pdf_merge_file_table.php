<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdfMergeFileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_merge_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('folder');
            $table->integer('size');
            $table->string('original_name');
            $table->string('server_name');
            $table->string('uin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_merge_files');
    }
}
