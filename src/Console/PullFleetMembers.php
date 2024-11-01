<?php

namespace Helious\SeatFAT\Console;

use Illuminate\Console\Command;
use Helious\SeatFAT\Jobs\ProcessCharacters;
use Helious\SeatFAT\Models\FATFleets;

/**
 * Class CheckBeaconFuel.
 *
 * @package Helious\SeatFAT\Console
 */
class PullFleetMembers extends Command
{
    /**
     * @var string
     */
    protected $signature = 'fats:update:fleets';

    /**
     * @var string
     */
    protected $description = 'FAT members of tracked ACTIVE fleets.';

    /**
     * Process the command.
     */
    public function handle()
    {
        $fleets = FATFleets::where('fleetActive', true)->get();
        
        foreach ($fleets as $fleet) {
          ProcessCharacters::dispatch($fleet->fleetID)
            ->onQueue('default');
        }

        $this->info('Jobs dispatched for active fleets.');
    }
}
