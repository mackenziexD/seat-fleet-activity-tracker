<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\EsiBase;
use Throwable;
use Helious\SeatFAT\Models\FATFleets;

abstract class AbstractFleetJob extends EsiBase
{
    protected $fleet_id;
    protected $token;

    public function __construct(int $fleet_id, $token)
    {
      parent::__construct();
      $this->token = $token;
      $this->fleet_id = $fleet_id;
    }

    public function handle()
    {
        if ($this->batchId && $this->batch()->cancelled()) {
            return;
        }

        logger()->debug(
            sprintf('[Jobs][%s] Fleet job is processing...', $this->job->getJobId()),
            [
                'fqcn' => static::class,
                'fleet_id' => $this->fleet_id,
            ]
        );
    }

    public function failed(Throwable $exception)
    {
      if ($exception->getCode() === 404) {
          // Mark the fleet as inactive
          FATFleets::where('fleetID', $this->fleet_id)->update(['fleetActive' => false]);
          \Log::error("Marking fleet ID: {$this->fleet_id} as inactive due to: " . $exception->getMessage());
      } else {
          // Log other exceptions or handle them as needed
          \Log::error("An error occurred: " . $e->getMessage());
      }
      
      parent::failed($exception);
    }
}
