<?php

Route::group([

    'namespace' => 'Helious\SeatFAT\Http\Controllers',
    'prefix' => 'fats',
    'middleware' => [
        'web',
        'auth',
        'can:seat-fleet-activity-tracker.access',
    ],
], function()
{
    Route::get('/dashboard', [
        'uses' => 'FATController@index',
        'as' => 'seat-fleet-activity-tracker::index',
    ]);

    Route::get('/stats', [
        'uses' => 'FATController@stats',
        'as' => 'seat-fleet-activity-tracker::stats',
    ]);

    Route::get('/track-fleet', [
        'uses' => 'FATController@trackFleet',
        'as' => 'seat-fleet-activity-tracker::trackFleet',
    ]);

    Route::post('/track-fleet', [
        'uses' => 'FATController@trackPostRequest',
        'as' => 'seat-fleet-activity-tracker::trackPostRequest',
    ]);

    Route::get('/fleets', [
        'uses' => 'FATController@AllFleets',
        'as' => 'seat-fleet-activity-tracker::allFleets',
    ]);

    Route::get('/track_fleet/{id}', [
        'uses' => 'FATController@fleet',
        'as' => 'seat-fleet-activity-tracker::fleet',
    ]);

});