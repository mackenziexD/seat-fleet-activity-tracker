<?php

Route::group([

    'namespace' => 'Helious\SeatFAT\Http\Controllers',
    'prefix' => 'fleet',
    'middleware' => [
        'web',
        'auth',
        'can:seat-fleet-activity-tracker.access',
    ],
], function()
{
  Route::get('/about', [
      'uses' => 'SeatHrController@about',
      'as' => 'seat-fleet-activity-tracker::about',
  ]);

});