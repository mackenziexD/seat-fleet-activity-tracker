<?php

namespace Helious\SeatFAT\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class ValidateFleetBoss extends AbstractFleetJob
{
  use Dispatchable;
  
  protected $method = 'get';
  protected $endpoint = '/fleets/{fleet_id}/';
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

    $statusCode = $response->getStatusCode();
    $status = $statusCode == 200 ? true : false;

    Cache::put("fleet_boss_validation_{$this->fleet_id}", $status, 300);
  }
}
