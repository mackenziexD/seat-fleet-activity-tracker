<?php

return [
    'seat-fleet-activity-tracker' => [
        'name' => 'Fleet Activity Tracker',
        'icon' => 'fas fa-rocket',
        'route_segment' => 'fats',
        'entries' => [
            [
                'name' => 'Dashboard',
                'icon' => 'fas fa-home',
                'route' => 'seat-fleet-activity-tracker::index',
                'permission' => [
                  'fats.access',
                ],
            ],
            [
                'name' => 'Track Fleet',
                'icon' => 'fas fa-hourglass-start',
                'route' => 'seat-fleet-activity-tracker::trackFleet',
                'permission' => [
                  'fats.track',
                ],
            ],
            [
                'name' => 'Stats',
                'icon' => 'fas fa-chart-pie',
                'route' => 'seat-fleet-activity-tracker::stats',
                'permission' => [
                  'fats.stats',
                ],
            ],
            [
                'name' => 'All Fleets',
                'icon' => 'fas fa-clock',
                'route' => 'seat-fleet-activity-tracker::allFleets',
                'permission' => [
                  'fats.allFleets',
                ],
            ]
        ],
    ],
];