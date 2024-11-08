<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Illuminate\Foundation\Bus\Dispatchable;
use Seat\Eveapi\Models\RefreshToken;

class ValidateFleet extends AbstractAuthCharacterJob
{
    use Dispatchable;
    
    protected $method = 'get';
    protected $endpoint = '/characters/{character_id}/fleet/';
    protected $version = 'v1';
    protected $scope = 'esi-fleets.read_fleet.v1';
    protected $tags = ['character'];

    public function __construct(RefreshToken $fleetBoss)
    {
      parent::__construct($fleetBoss);
    }

    public static function validateFleet(RefreshToken $fleetBoss)
    {
      $job = new static($fleetBoss);
      return $job->performValidation();
    }

    public function performValidation()
    {
      try {
        $response = $this->retrieve([
            'character_id' => $this->getCharacterId(),
        ]);

        $body = $response->getBody();
        if ($response->getStatusCode() == 200) {
            return $body->fleet_id;
        }
      } catch (\Exception $e) {
        logger()->error("Fleet validation failed: " . $e->getMessage());
      }
      return false;
    }
}