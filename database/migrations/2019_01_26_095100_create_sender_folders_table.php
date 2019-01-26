<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\SettingsController;

class CreateSenderFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sender_folders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('');
            $table->integer('owner')
                ->default(SettingsController::take('SYSTEM_USER_ID'))
                ->references('id')->on('api_users')->onDelete('restrict');
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
        Schema::dropIfExists('sender_folders');
    }
}