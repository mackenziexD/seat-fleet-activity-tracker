<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Helious\SeatFAT\Models\FATFleets;
use Helious\SeatFAT\Models\FATS;
use Helious\SeatFAT\Models\TrackedCorps;
use Helious\SeatFAT\Jobs\ValidateFleetId;
use Helious\SeatFAT\Jobs\ValidateFleetBoss;

/**
 * Class HomeController.
 *
 * @package Author\Seat\YourPackage\Http\Controllers
 */
class FATController extends Controller
{

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
        return redirect()->route('seat-fleet-activity-tracker::trackFleet')->with('error', 'Fleet boss does not have esi-fleets.read_fleet.v1 scopes.');
        }

        // make sure the character is in a fleet and get the fleet_id
        try {
            ValidateFleetId::dispatchSync($fleetBoss);
            $fleet_id = Cache::get("fleet_validation_{$fleetBoss->character_id}");
        } catch (\Exception $e) {
            $fleet_id = null;
        }

        if(!$fleet_id){
            Cache::forget("fleet_validation_{$fleetBoss->character_id}");
            return redirect()->route('seat-fleet-activity-tracker::trackFleet')
                ->with('error', 'You need to be in a fleet, and you need to be the fleet boss!');
        }
        
        // Validate if the character is the fleet boss
        try {
            ValidateFleetBoss::dispatchSync($fleet_id, $fleetBoss);
            $is_fleetBoss = Cache::get("fleet_boss_validation_{$fleet_id}");
        } catch (\Exception $e) {
            $is_fleetBoss = null;
        }
        
        if(!$is_fleetBoss){
            Cache::forget("fleet_boss_validation_{$fleet_id}");
            return redirect()->route('seat-fleet-activity-tracker::trackFleet')
                ->with('error', 'Not Fleet Boss! Only the fleet boss can track a fleet.');
        }

        // Clear cache as we know we passed both fleetid and fleetboss checks
        Cache::forget("fleet_validation_{$fleetBoss->character_id}");
        Cache::forget("fleet_boss_validation_{$fleet_id}");
        
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
        // Get the tracked corporation IDs from TrackedCorps model
        $trackedCorporationIds = TrackedCorps::pluck('corporation_id')->toArray();
    
        // Get fleets within the last 30 days (no corporation filter needed here)
        $fleets = FATFleets::where('created_at', '>=', now()->subDays(30))->get();
        
        // Get FATS entries with characters in tracked corporations only
        $fatsEntries = FATS::with(['character.affiliation'])
            ->whereHas('character.affiliation', function($query) use ($trackedCorporationIds) {
                $query->whereIn('corporation_id', $trackedCorporationIds);
            })
            ->get();
    
        // Initialize data arrays
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
    
        // Generate date ranges for monthly and daily stats
        $currentDate = now();
        for ($i = 0; $i < 36; $i++) {
            $month = $currentDate->copy()->subMonths($i)->format('Y-m');
            $monthlyStats[$month] = 0; 
        }
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
    
            // Group by corporation only for tracked corporations
            $corporationId = $fat->character->affiliation->corporation_id;
            $corporationName = $fat->character->affiliation->corporation->name;
            
            if (in_array($corporationId, $trackedCorporationIds)) {
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
    
        // Return data to the view
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

    public function settings()
    {
        $all_corporations = CorporationInfo::all();
        $tracked_corps = TrackedCorps::all();
        return view('seat-fleet-activity-tracker::settings', compact('all_corporations', 'tracked_corps'));
    }

  public function postAddTrackedCorp(Request $request)
  {
    if ($request->has('corporations')) {
        foreach ($request->input('corporations') as $corp) {
            TrackedCorps::firstOrCreate(
                ['corporation_id' => $corp]
            );
        }
    }
    
    return redirect()->back()
        ->with('success', 'Tracked Corps added!');
  }
  
  public function deletedTrackedCorp($id)
  {
    TrackedCorps::findOrFail($id)->delete();
    
    return redirect()->back()
        ->with('success', 'Tracked Corps removed!');
  }
}