<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;

class FATFleets extends Model
{

    protected $table = 'seat_fat_fleets';

    protected $fillable = [
        'fleetName',
        'fleetType',
        'fletActive',
    ];

}
