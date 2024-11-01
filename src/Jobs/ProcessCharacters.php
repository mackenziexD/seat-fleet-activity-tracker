<?php

namespace Helious\SeatFAT\Jobs;

use Helious\SeatFAT\Jobs\AbstractFleetJob;

class ProcessCharacters extends AbstractFleetJob
{
    protected $method = 'get';
    protected $endpoint = '/fleets/{fleet_id}/members/';
    protected $version = 'v5';
    protected $scope = 'esi-fleets.read_fleet.v1';
    protected $tags = ['fleet'];

    protected $fleet_id;
    protected $token;

    public function __construct($fleet_id, $token)
    {
        parent::__construct($fleet_id);

        $this->fleet_id = $fleet_id;
        $this->token = $token;
    }

    public function handle()
    {
        \Log::info($this->fleet_id);
        $response = $this->retrieve([
            'fleet_id' => $this->fleet_id
        ]);

        $members = collect($response->getBody());

        \Log::info($members);
    }
}
