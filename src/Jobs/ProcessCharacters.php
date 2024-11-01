<?php

namespace Seat\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;

/**
 * Class Blueprints.
 *
 * @package Seat\Eveapi\Jobs\Character
 */
class ProcessCharacters extends AbstractAuthCharacterJob
{
    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/fleets/{fleet_id}/members/';

    /**
     * @var int
     */
    protected $version = 'v5';

    /**
     * @var string
     */
    protected $scope = 'esi-fleets.read_fleet.v1';

    /**
     * @var array
     */
    protected $tags = ['fleet'];

    protected $fleet_id;

    public function __construct($fleet_id)
    {
      parent::__construct();

      $this->fleet_id = $fleet_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function handle()
    {

      $response = $this->retrieve([
        'fleet_id' => $this->fleet_id,
      ]);

      $members = collect($response->getBody());

      \Log::info($members);

    }

}