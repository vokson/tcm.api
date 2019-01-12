<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\SettingsController;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filename');
            $table->integer('status');
            $table->integer('mistake_count');
            $table->integer('owner')
                ->default(SettingsController::take('SYSTEM_USER_ID'))
                ->references('id')->on('api_users')->onDelete('restrict');
            $table->boolean('is_attachment_exist')->default(false);
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
        Schema::dropIfExists('checks');
    }
}