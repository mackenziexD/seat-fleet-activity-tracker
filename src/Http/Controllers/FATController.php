<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helious\SeatFAT\Models\FATFleets;
use Helious\SeatFAT\Models\FATS;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;
use Helious\SeatFAT\Services\FATEsiToken;

/**
 * Class HomeController.
 *
 * @package Author\Seat\YourPackage\Http\Controllers
 */
class FATController extends Controller
{
  
  /**
   * @var \Seat\Services\Contracts\EsiClient
   */
  private EsiClient $esi;

  public function __construct(EsiClient $client)
  {
      $this->esi = $client;
  }

  public function index() 
  {
    $characters = auth()->user()->characters;
    $characterIds = $characters->pluck('character_id')->toArray(); 
    $fleets = FATS::whereIn('character_id', $characterIds)
        ->with('character', 'ship', 'fleet')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    $characterStats = [];
    foreach ($characters as $character) {
        $fatsCount = FATS::where('character_id', $character->character_id)->count();

        $characterStats[] = [
            'id' => $character->character_id,
            'name' => $character->name,
            'fats_count' => $fatsCount,
        ];
    }

    // Sort characterStats by fats_count in descending order
    usort($characterStats, function ($a, $b) {
        return $b['fats_count'] <=> $a['fats_count'];
    });

    $topCharacterStats = array_slice($characterStats, 0, 10);

    return view('seat-fleet-activity-tracker::index', compact('fleets', 'topCharacterStats'));
}

  public function allFleets() {
    $fleets = FATFleets::all();

    return view('seat-fleet-activity-tracker::allFleets', compact('fleets'));
  }

  public function trackFleet(){
    $characters = auth()->user()->characters;
    return view('seat-fleet-activity-tracker::track', compact('characters'));
  }

  public function trackPostRequest(Request $request) {
    $request->validate([
        'fleet_name' => 'required|max:255',
        'fleet_boss' => 'required',
        'fleet_type' => 'nullable',
    ]);

    $fleetBoss = RefreshToken::where('character_id', $request->input('fleet_boss'))->first();
    if(!$fleetBoss->token || !$fleetBoss->refresh_token){
      return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'Could not find fleet boss on seat, try to re-link the character.');
    }

    $fleet = $this->checkFleetIdIsCorrect($fleetBoss, $request->input('fleet_id'));
    if (!$fleet) {
      return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'Only fleet boss can track a fleet!');
    }

    // Check if the fleet is already being tracked
    $existingFleet = FATFleets::where('fleetID', $fleet->fleet_id)
        ->where('fleetCommander', $fleetBoss->character_id)
        ->where('fleetActive', true)
        ->first();

    if ($existingFleet) {
        return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'This fleet is already being tracked.'); // Inform the user that the fleet is already being tracked
    }

    // Save the fleet details
    $savedFleet = FATFleets::create([
        'fleetName' => $request->input('fleet_name'),
        'fleetType' => $request->input('fleet_type'),
        'fleetID' => $fleet->fleet_id,
        'fleetCommander' => $fleetBoss->character_id,
        'fleetActive' => true,
    ]);

    return redirect()->route('seat-fleet-activity-tracker::fleet', ['id' => $savedFleet->fleetID]);
  }


  public function fleet($id) {
    $fleet = FATFleets::where('fleetID', $id)->firstOrFail();
    $members = FATS::where('fleetID', $id)->with('character', 'solar_system', 'ship')->get();

    $shipTypeCounts = $members->groupBy(function ($member) {
      return $member->ship->typeName; 
    })->map(function ($group) {
        return $group->count(); 
    })->sortDesc();

    return view('seat-fleet-activity-tracker::fleet', compact('fleet', 'members', 'shipTypeCounts'));
  }

  private function checkFleetIdIsCorrect($fleetBoss)
  {
    try {
        $esiToken = new FATEsiToken();
        $esiToken->setAccessToken($fleetBoss->token);
        $esiToken->setRefreshToken($fleetBoss->refresh_token);
        
        if ($fleetBoss->expires_on) {
          $esiToken->setExpiresOn(new \DateTime($fleetBoss->expires_on));
        }
        
        $this->esi->setAuthentication($esiToken);
        $response = $this->esi->invoke('get', '/characters/{character_id}/fleet/', [
          'character_id' => $fleetBoss->character_id,
        ]);
        $body = $response->getBody();

        if ($response->getStatusCode() == 200) return $body;
    } catch (\Exception $e) {
        return false;
    }
  }

  public function stats()
  {
    $fatsEntries = FATS::with(['character.affiliation'])
        ->get();

    $monthlyStats = [];
    $yearlyStats = [];

    foreach ($fatsEntries as $fat) {
        $createdAt = $fat->created_at;
        $monthYear = $createdAt->format('Y-m');
        $year = $createdAt->format('Y'); 
        
        // Group by corporation
        $corporationId = $fat->character->affiliation->corporation_id;
        $corporationName = $fat->character->affiliation->corporation->name;

        // Monthly Stats
        if (!isset($monthlyStats[$monthYear][$corporationId])) {
            $monthlyStats[$monthYear][$corporationId] = [
                'corporation_name' => $corporationName,
                'fats_count' => 0,
                'member_count' => 0,
            ];
        }
        $monthlyStats[$monthYear][$corporationId]['fats_count']++;

        // Yearly Stats
        if (!isset($yearlyStats[$year][$corporationId])) {
            $yearlyStats[$year][$corporationId] = [
                'corporation_name' => $corporationName,
                'fats_count' => 0,
                'member_count' => 0,
            ];
        }
        $yearlyStats[$year][$corporationId]['fats_count']++;
    }

    // Fetch member counts from corporation_infos
    $corporationInfos = \DB::table('corporation_infos')
        ->select('corporation_id', 'member_count')
        ->get()
        ->keyBy('corporation_id');

    // Calculate averages and populate stats
    foreach ($monthlyStats as $month => $corporations) {
      foreach ($corporations as $corporationId => $data) {
          if (isset($corporationInfos[$corporationId])) {
              $memberCount = $corporationInfos[$corporationId]->member_count;
              $monthlyStats[$month][$corporationId]['member_count'] = $memberCount;
              $monthlyStats[$month][$corporationId]['avg_pap'] = $memberCount > 0
                  ? round($data['fats_count'] / $memberCount, 2) // Round to 2 decimal places
                  : 0; // Avoid division by zero
          }
        }
    }
    
    foreach ($yearlyStats as $year => $corporations) {
        foreach ($corporations as $corporationId => $data) {
            if (isset($corporationInfos[$corporationId])) {
                $memberCount = $corporationInfos[$corporationId]->member_count;
                $yearlyStats[$year][$corporationId]['member_count'] = $memberCount;
                $yearlyStats[$year][$corporationId]['avg_pap'] = $memberCount > 0
                    ? round($data['fats_count'] / $memberCount, 2) // Round to 2 decimal places
                    : 0; // Avoid division by zero
            }
        }
    }

    // Format the stats for easier rendering
    $formattedMonthlyStats = [];
    foreach ($monthlyStats as $month => $corporations) {
        $formattedMonthlyStats[$month] = array_values($corporations);
    }

    $formattedYearlyStats = [];
    foreach ($yearlyStats as $year => $corporations) {
        $formattedYearlyStats[$year] = array_values($corporations);
    }

    return view('seat-fleet-activity-tracker::stats', [
      'monthlyStats' => $formattedMonthlyStats,
      'yearlyStats' => $formattedYearlyStats,
    ]);
  }

}
