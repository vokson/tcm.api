<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Http\Controllers\SettingsController;

class AddOwnerToLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->integer('owner')
                ->default(SettingsController::take('SYSTEM_USER_ID'))
                ->references('id')->on('api_users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('owner');
        });
    }
}
