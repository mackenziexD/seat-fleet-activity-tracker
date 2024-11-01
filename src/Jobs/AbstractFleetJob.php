<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\EsiBase;

abstract class AbstractFleetJob extends EsiBase
{
    protected $fleet_id;

    public function __construct(int $fleet_id)
    {
      parent::__construct(); 
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
}
