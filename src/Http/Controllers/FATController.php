<?php

namespace Helious\SeatFAT\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;

/**
 * Class HomeController.
 *
 * @package Author\Seat\YourPackage\Http\Controllers
 */
class RattingTaxController extends Controller
{

  public function about(){
    return view('seat-ratting-taxes::index', compact('totalAmountThisMonth', 'totalAmountLastMonth', 'uniqueSystemNames'));
  }

}