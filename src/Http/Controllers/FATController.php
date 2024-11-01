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

  public function index() {
    // $characters = auth()->user()->characters;
    // $characterIds = $characters->pluck('character_id')->toArray(); 
    // $fleets = FATS::whereIn('character_id', $characterIds)->get();

    return view('seat-fleet-activity-tracker::index');
  }

  public function allFleets() {
    $fleets = FATFleets::all();

    return view('seat-fleet-activity-tracker::allFleets', compact('fleets'));
  }


  public function about(){
    return view('seat-fleet-activity-tracker::about');
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
    return view('seat-fleet-activity-tracker::fleet', compact('fleet', 'members'));
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

}
