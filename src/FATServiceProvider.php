<?php

namespace Helious\SeatFAT;

use Seat\Services\AbstractSeatPlugin;
use Helious\SeatFAT\Commands\PullFleetMembers;

class FATServiceProvider extends AbstractSeatPlugin
{
    public function register()
    {
        
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-fleet-activity-tracker.config.php', 'seat-fleet-activity-tracker.config');
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-fleet-activity-tracker.locale.php', 'seat-fleet-activity-tracker.locale');
        $this->registerPermissions(__DIR__ . '/Config/seat-fleet-activity-tracker.permissions.php', 'seat-fleet-activity-tracker');
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-fleet-activity-tracker.sidebar.php', 'package.sidebar');

        
        $this->registerDatabaseSeeders([
            \Helious\SeatFAT\Database\Seeders\ScheduleSeeder::class,
        ]);
        
    }

    public function boot()
    {
        $this->addCommands();
        $this->add_translations();

        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'seat-fleet-activity-tracker');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    }

    private function addCommands() 
    {
        $this->commands([
            PullFleetMembers::class,
        ]);
    }

    private function add_translations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'seat-fleet-activity-tracker');
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     * @example SeAT Web
     *
     */
    public function getName(): string
    {
        return 'SeAT Fleet Activity Tracker';
    }

    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/mackenziexD/seat-fleet-activity-tracker';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     * @example web
     *
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-fleet-activity-tracker';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     * @example eveseat
     *
     */
    public function getPackagistVendorName(): string
    {
        return 'helious';
    }
}