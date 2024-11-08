<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helious\SeatFAT\Models\FATFleets;
use Helious\SeatFAT\Models\FATS;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Services\Contracts\EsiClient;
use Helious\SeatFAT\Services\FATEsiToken;
use Helious\SeatFAT\Jobs\ValidateFleet;
use Illuminate\Support\Facades\Bus;

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
    if(!in_array('esi-fleets.read_fleet.v1', $fleetBoss->getScopes())){
      return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'Could not find fleet boss on seat, try to re-link the character.');
    }

    $fleet_id = ValidateFleet::validateFleet($fleetBoss);
    if (!$fleet_id) {
        return redirect()->route('seat-fleet-activity-tracker::trackFleet')
            ->with('error', 'Only fleet boss can track a fleet.');
    }
    
    $existingFleet = FATFleets::where('fleetID', $fleet_id)
        ->where('fleetCommander', $fleetBoss->character_id)
        ->where('fleetActive', true)
        ->first();

    if ($existingFleet) {
        return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'This fleet is already being tracked.');
    }

    $savedFleet = FATFleets::create([
        'fleetName' => $request->input('fleet_name'),
        'fleetType' => $request->input('fleet_type'),
        'fleetID' => $fleet_id,
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

  public function stats()
  {
      // Hate i have done this, want to rewrite it all.
    
      $fleets = FATFleets::where('created_at', '>=', now()->subDays(30))->get();
      $fatsEntries = FATS::with(['character.affiliation'])->get();
  
      $monthlyStats = [];
      $dailyFats = [];
      $dailyFleets = [];
      $topFCs = [];
      $topCorps = [];
      $corpSizes = []; 
      $otherData = []; 
      $timezoneSplit = [
          'EU' => 0,
          'AU' => 0,
          'US' => 0,
      ];
  
      // Generate a 3-year rolling range of months
      $currentDate = now();
      for ($i = 0; $i < 36; $i++) {
          $month = $currentDate->copy()->subMonths($i)->format('Y-m');
          $monthlyStats[$month] = 0; 
      }
  
      // Generate a 1-month rolling range of days
      $currentDate = now();
      for ($i = 0; $i < 30; $i++) {
          $day = $currentDate->copy()->subDays($i)->format('Y-m-d');
          $dailyFats[$day] = 0; 
          $dailyFleets[$day] = 0; 
      }
  
      // Calculate Daily Fleets from the last 30 days of fleets
      foreach ($fleets as $fleet) {
          $day = $fleet->created_at->format('Y-m-d');
          if (isset($dailyFleets[$day])) {
              $dailyFleets[$day]++;
          }
  
          // For Top FCs
          $fcName = $fleet->fleet_commander->name;
          if (!isset($topFCs[$fcName])) {
              $topFCs[$fcName] = 0;
          }
          $topFCs[$fcName]++;
      }
  
      // Monthly Stats and Daily Fats from all FATS entries
      foreach ($fatsEntries as $fat) {
          $createdAt = $fat->created_at;
          $monthYear = $createdAt->format('Y-m');
          $day = $createdAt->format('Y-m-d');
  
          // For Monthly Total Fats
          if (isset($monthlyStats[$monthYear])) {
              $monthlyStats[$monthYear]++;
          }
  
          // For Daily Fats
          if (isset($dailyFats[$day])) {
              $dailyFats[$day]++;
          }
  
          // Group by corporation
          $corporationId = $fat->character->affiliation->corporation_id;
          $corporationName = $fat->character->affiliation->corporation->name;
  
          if (!isset($topCorps[$corporationName])) {
              $topCorps[$corporationName] = 0;
          }
          $topCorps[$corporationName]++;
  
          // For Timezone FAT Split
          $hour = (int) $createdAt->format('H');
          if ($hour >= 0 && $hour < 8) {
              $timezoneSplit['US']++;
          } elseif ($hour >= 8 && $hour < 16) {
              $timezoneSplit['AU']++;
          } else {
              $timezoneSplit['EU']++;
          }
      }
  
      // Fetch member counts for only the top corporations using their names
      $corporationNames = array_keys($topCorps);
      $corporationInfos = \DB::table('corporation_infos')
          ->whereIn('name', $corporationNames)
          ->select('name', 'corporation_id', 'member_count') 
          ->get()
          ->keyBy('name'); 

      // Prepare data for FATS Relative To Corp Size
      foreach ($topCorps as $corporationName => $fatsCount) {
          $corporationInfo = $corporationInfos->get($corporationName);

          if ($corporationInfo) {
              $size = $corporationInfo->member_count;
              $corpSizes[$corporationName] = $size;
              $otherData[$corporationName] = $size > 0 ? round(($fatsCount / $size) * 100, 2) : 0;
          }
      }

      // Sort in descending order for top corporations
      arsort($topCorps);
  
      return view('seat-fleet-activity-tracker::stats', [
          'monthlyStats' => $monthlyStats,
          'dailyFats' => $dailyFats, 
          'dailyFleets' => $dailyFleets, 
          'timezoneSplit' => $timezoneSplit,
          'topCorps' => $topCorps,
          'topFCs' => $topFCs,
          'corpSizes' => $corpSizes, 
          'otherData' => $otherData 
      ]);
  }   
  

}
