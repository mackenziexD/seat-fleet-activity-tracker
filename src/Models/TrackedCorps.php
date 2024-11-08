<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class TrackedCorps extends Model
{

  protected $table = 'seat_fat_tracked_corps';
  protected $primaryKey = 'corporation_id';

  protected $fillable = [
    'corporation_id'
  ];

  public function corporation()
  {
    return $this->belongsTo(CorporationInfo::class, 'corporation_id', 'corporation_id');
  }


}