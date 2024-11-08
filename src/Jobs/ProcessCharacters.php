<?php

namespace Helious\SeatFAT\Jobs;

use Helious\SeatFAT\Jobs\AbstractFleetJob;
use Helious\SeatFAT\Models\FATS;
use Carbon\Carbon;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Jobs\Character\Info;
use Illuminate\Support\Facades\Bus;

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

        $response = $this->retrieve([
            'fleet_id' => $this->fleet_id
        ]);

        $members = $response->getBody();
        $unknownCharacterJobs = [];

        collect($members)->each(function ($member) use (&$unknownCharacterJobs) {
            FATS::insertOrIgnore([
                'character_id' => $member->character_id,
                'solar_system_id' => $member->solar_system_id,
                'ship_type_id' => $member->ship_type_id,
                'fleetID' => $this->fleet_id,
                'created_at' => Carbon::now(),
            ]);

            if (!CharacterInfo::where('character_id', $member->character_id)->exists()) {
                $unknownCharacterJobs[] = new Info($member->character_id);
            }
        });

        // Dispatch all Info jobs in batch for unknown characters
        if (!empty($unknownCharacterJobs)) {
            Bus::batch($unknownCharacterJobs)->onQueue('default')->dispatch();
        }
    }
}
