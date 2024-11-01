<?php

namespace Helious\SeatFAT\Jobs;

use Helious\SeatFAT\Jobs\AbstractFleetJob;
use Helious\SeatFAT\Models\FATS;
use Helious\SeatFAT\Models\FATFleets;
use Carbon\Carbon;
use Helious\SeatFAT\Jobs\UnknownFleetMembers;
use Seat\Eveapi\Models\Universe\UniverseName;

class ProcessCharacters extends AbstractFleetJob
{
    protected $method = 'get';
    protected $endpoint = '/fleets/{fleet_id}/members/';
    protected $version = 'v1';
    protected $scope = 'esi-fleets.read_fleet.v1';
    protected $tags = ['fleet'];

    public function __construct($fleet_id, $token)
    {
        parent::__construct($fleet_id, $token);
    }

    public function handle()
    {
      parent::handle();
      \Log::error("Processing fleet ID: " . $this->fleet_id);
  
      $response = $this->retrieve([
          'fleet_id' => $this->fleet_id
      ]);

      $members = $response->getBody();
      collect($members)->each(function ($member) {
          FATS::insertOrIgnore([
              'character_id' => $member->character_id,
              'solar_system_id' => $member->solar_system_id,
              'ship_type_id' => $member->ship_type_id,
              'fleetID' => $this->fleet_id,
              'created_at' => Carbon::now(),
          ]);

          $isKnownCharacter = UniverseName::where('entity_id', $member->character_id)->exists();

          if (!$isKnownCharacter) {
              UnknownFleetMembers::dispatch([$member->character_id])
                  ->onQueue('default');
          }
      });
    }

}
