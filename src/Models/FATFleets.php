<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;

class FATFleets extends Model
{

    protected $table = 'seat_fat_fleets';

    protected $fillable = [
        'fleetID',
        'fleetName',
        'fleetType',
        'fleetActive',
        'fleetCommander',
    ];

    public function fleet_commander()
    {
        return $this->hasOne(CharacterInfo::class, 'character_id', 'fleetCommander');
    }

}
