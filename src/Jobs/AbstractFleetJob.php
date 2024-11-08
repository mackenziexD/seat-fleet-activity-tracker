<?php

namespace Helious\SeatFAT\Jobs;

use Seat\Eveapi\Jobs\EsiBase;
use Throwable;
use Helious\SeatFAT\Models\FATFleets;
use Seat\Eseye\Exceptions\RequestFailedException;

abstract class AbstractFleetJob extends EsiBase
{
    const CHARACTER_NOT_FLEET_BOSS = "The fleet does not exist or you don't have access to it!";
    
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
        if ($exception instanceof RequestFailedException) {
            if ($exception->getError() === self::CHARACTER_NOT_FLEET_BOSS) {
                if (FATFleets::where('fleetID', $this->fleet_id)->exists()) {
                    FATFleets::where('fleetID', $this->fleet_id)->update(['fleetActive' => false]);
                    \Log::error("Fleet ID {$this->fleet_id} marked inactive. Reason: " . $exception->getMessage());
                } else {
                    \Log::warning("Fleet validation failed, but fleet ID {$this->fleet_id} was not found in database.");
                }
            }
        }
        
        parent::failed($exception);
    }
    
}
