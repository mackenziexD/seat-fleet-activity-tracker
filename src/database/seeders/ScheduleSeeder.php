<?php

namespace Helious\SeatFAT\Database\Seeders;

use Seat\Services\Seeding\AbstractScheduleSeeder;

class ScheduleSeeder extends AbstractScheduleSeeder
{

    public function getSchedules(): array
    {
      return [
          [   // FATS Process Fleet Members | Every Minute
              'command' => 'fats:update:fleets',
              'expression' => '* * * * *',
              'allow_overlap' => false,
              'allow_maintenance' => false,
              'ping_before' => null,
              'ping_after' => null,
          ],
      ];
    }

    public function getDeprecatedSchedules(): array
    {
        return [];
    }
}