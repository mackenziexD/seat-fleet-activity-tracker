<?php

namespace Helious\SeatFAT\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class CharacterInfos extends CharacterInfo
{
  public function corporation()
  {
      return $this->belongsTo(CorporationInfo::class, 'corporation_id', 'corporation_id'); // Ensure this is correct
  }
}