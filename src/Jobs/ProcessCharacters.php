<?php

namespace Helious\SeatFAT\Jobs;

use Helious\SeatFAT\Jobs\AbstractFleetJob;
use Helious\SeatFAT\Models\FATS;

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

        collect($members)->each(function ($members) {
          FATS::insertOrIgnore([
            'character_id' => $members->character_id,
            'solar_system_id' => $members->solar_system_id,
            'ship_type_id' => $members->ship_type_id,
            'fleetID' => $this->fleet_id,
          ]);
        });

    }
}
