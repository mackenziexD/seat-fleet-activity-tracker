<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helious\SeatFAT\Models\Fleets;
use Seat\Eveapi\Models\RefreshToken;

/**
 * Class HomeController.
 *
 * @package Author\Seat\YourPackage\Http\Controllers
 */
class FATController extends Controller
{

  public function index(){
    return view('seat-fleet-activity-tracker::index');
  }

  public function about(){
    return view('seat-fleet-activity-tracker::about');
  }

  public function trackFleet(){
    $characters = auth()->user()->characters;
    return view('seat-fleet-activity-tracker::track', compact('characters'));
  }

  public function trackPostRequest(Request $request){
    $validated = $request->validate([
        'fleet_name' => 'required|max:255',
        'fleet_id' => 'required|integer',
        'fleet_boss' => 'required',
        'fleet_type' => 'nullable|array',
    ]);

    $bossToken = RefreshToken::where('character_id', $request->input('fleet_boss'));

    $fleet = $this->checkFleetIdIsCorrect($bossToken, $request->input('fleet_id'));
    if(!$fleet) return view('seat-fleet-activity-tracker::track')->with('error', 'Cant find matching fleet with supplied fleet id, you need to be in fleet and you need to be the fleet boss!');

    $savedFleet = Fleets::Create([
      'fleetName' => $request->input('fleet_name'),
      'fleetType' => $request->input('fleet_type')
    ]);

    return route('seat-fleet-activity-tracker::fleet', ['id'=> $savedFleet->id]);
  }

  private function checkFleetIdIsCorrect($bossToken, $fleetId){
    $esi = app('esi-client')->get();
    try {
        $response = $esi->invoke('get', '/fleets/{fleet_id}/', [
            'fleet_id' => $fleetId,
            'token' => $bossToken
        ]);
        return true;
    } catch (\Exception $e) {
      return false;
    }
  }

}
