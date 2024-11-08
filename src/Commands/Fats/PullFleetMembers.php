<?php

namespace Helious\SeatFAT\Commands\Fats;

use Illuminate\Console\Command;
use Helious\SeatFAT\Jobs\ProcessCharacters;
use Helious\SeatFAT\Models\FATFleets;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class PullFleetMembers.
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
        $token = RefreshToken::where('character_id', $fleet->fleetCommander)->first();

        if ($token) {
          ProcessCharacters::dispatch($fleet->fleetID, $token)
            ->onQueue('default');
        } else {
          $this->warn("No refresh token found for fleet commander ID {$fleet->fleetCommander}");
        }
      }
  
      $this->info('Jobs dispatched for active fleets.');
    }
}
