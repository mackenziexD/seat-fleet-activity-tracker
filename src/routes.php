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
    Route::get('/dashboard', [
        'uses' => 'FATController@index',
        'as' => 'seat-fleet-activity-tracker::index',
    ]);

    Route::get('/track-fleet', [
        'uses' => 'FATController@trackFleet',
        'as' => 'seat-fleet-activity-tracker::trackFleet',
    ]);

    Route::post('/track-fleet', [
        'uses' => 'FATController@trackPostRequest',
        'as' => 'seat-fleet-activity-tracker::trackPostRequest',
    ]);

    Route::get('/fleet/{id}', [
        'uses' => 'FATController@fleet',
        'as' => 'seat-fleet-activity-tracker::fleet',
    ]);

    Route::get('/about', [
        'uses' => 'FATController@about',
        'as' => 'seat-fleet-activity-tracker::about',
    ]);

});