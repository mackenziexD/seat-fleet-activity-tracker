<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Eveapi\Models\Sde\SolarSystem;
use Seat\Eveapi\Models\Universe\UniverseName;
use Helious\SeatFAT\Models\FATFleets;

class FATS extends Model
{

    protected $table = 'seat_fats';

    public $timestamps = true;

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

    public function fleet(){
        return $this->hasOne(FATFleets::class, 'fleetID', 'fleetID');
    }

    public function affiliation(){
        return $this->belongsTo(CharacterAffiliation::class, 'character_id', 'character_id');
    }

}
