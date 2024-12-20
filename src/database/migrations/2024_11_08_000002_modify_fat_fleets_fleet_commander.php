<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFatFleetsFleetCommander extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('seat_fat_fleets', function (Blueprint $table) {
          $table->bigInteger('fleetCommander')->change();
          $table->bigInteger('fleetID')->change();
      });

      Schema::table('seat_fats', function (Blueprint $table) {
          $table->bigInteger('fleetID')->change(); 
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
        $table->bigInteger('fleetCommander')->change();
        $table->bigInteger('fleetID')->change();
      });
      Schema::table('seat_fats', function (Blueprint $table) {
        $table->bigInteger('fleetID')->change(); 
      });
    }
}
