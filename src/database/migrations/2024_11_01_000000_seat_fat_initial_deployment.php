<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeatFatInitialDeployment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_fat_fleets', function (Blueprint $table) {
            $table->increments('id');
            $table->text('fleetName');
            $table->text('fleetType');
            $table->integer('fleetCommander');
            $table->boolean('fletActive')->default(true);
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
        Schema::dropIfExists('seat_fat_fleets');
    }
}