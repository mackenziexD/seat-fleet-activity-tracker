<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helious\SeatFAT\Models\FATFleets;
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
    $request->validate([
      'fleet_name' => 'required|max:255',
      'fleet_id' => 'required|integer',
      'fleet_boss' => 'required',
      'fleet_type' => 'nullable',
    ]);

    $bossToken = RefreshToken::where('character_id', $request->input('fleet_boss'))->first();

    $fleet = $this->checkFleetIdIsCorrect($bossToken, $request->input('fleet_id'));
    if(!$fleet) return view('seat-fleet-activity-tracker::track')->with('error', 'Cant find matching fleet with supplied fleet id, you need to be in fleet and you need to be the fleet boss!');

    $savedFleet = FATFleets::Create([
      'fleetName' => $request->input('fleet_name'),
      'fleetType' => $request->input('fleet_type'),
      'fleetCommander' => $bossToken->character_id,
      'fletActive' => true,
    ]);

    return route('seat-fleet-activity-tracker::fleet', ['id'=> $savedFleet->id]);
  }

  public function fleet($id) {

    return view('seat-fleet-activity-tracker::track', compact('characters'));
  }

  private function checkFleetIdIsCorrect($bossToken, $fleetId)
  {
      try {
          $esiToken = new FATEsiToken();
          $esiToken->setAccessToken($bossToken->token);
          $esiToken->setRefreshToken($bossToken->refresh_token);
          
          if ($bossToken->expires_on) {
              $esiToken->setExpiresOn(new \DateTime($bossToken->expires_on));
          }
          
          $this->esi->setAuthentication($esiToken);
          $response = $this->esi->invoke('get', '/fleets/{fleet_id}/', [
              'fleet_id' => $fleetId,
          ]);

          if ($response->getStatusCode() == 200) return true;
      } catch (\Exception $e) {
          return false;
      }
  }

}
