<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\SolarSystem;

class FATS extends Model
{

    protected $table = 'seat_fat_fleets';

    protected $fillable = [
        'character_id',
        'solar_system_id',
        'ship_type_id',
        'fleetID'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function solar_system()
    {
      return $this->hasOne(SolarSystem::class, 'system_id', 'solar_system_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function character()
    {
      return $this->hasOne(UniverseName::class, 'entity_id', 'character_id')
          ->withDefault([
              'name' => trans('web::seat.unknown'),
              'category' => 'character',
          ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ship()
    {
      return $this->hasOne(InvType::class, 'typeID', 'ship_type_id')
          ->withDefault([
              'typeName' => trans('web::seat.unknown'),
          ]);
    }

}
