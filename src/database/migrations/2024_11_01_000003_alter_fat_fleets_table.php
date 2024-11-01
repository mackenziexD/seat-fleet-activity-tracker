<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeatFatAlterFatFleets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('seat_fat_fleets', function (Blueprint $table) {
        $table->renameColumn('fletActive', 'fleetActive');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('seat_fat_fleets', function (Blueprint $table) {
        $table->renameColumn('fleetActive', 'fletActive');
      });
    }
}