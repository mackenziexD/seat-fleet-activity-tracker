<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class ValidateFleetId extends AbstractAuthCharacterJob
{
  use Dispatchable;
  
  protected $method = 'get';
  protected $endpoint = '/characters/{character_id}/fleet/';
  protected $version = 'v1';
  protected $scope = 'esi-fleets.read_fleet.v1';
  protected $tags = ['character'];

  public function __construct($character_id)
  {
      parent::__construct($character_id);
  }

  public function handle()
  {
    parent::handle();

    $response = $this->retrieve([
        'character_id' => $this->getCharacterId(),
    ]);

    $statusCode = $response->getStatusCode();
    $fleetId = $statusCode == 200 ? $response->getBody()->fleet_id : null;

    Cache::put("fleet_validation_{$this->getCharacterId()}", $fleetId, 300);
  }
}
