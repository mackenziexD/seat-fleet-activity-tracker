<?php

namespace Helious\SeatFAT;

use Seat\Services\AbstractSeatPlugin;
use Helious\SeatFAT\Console\PullFleetMembers;

class FATServiceProvider extends AbstractSeatPlugin
{
    public function register()
    {
        
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-fleet-activity-tracker.php', 'seat-fleet-activity-tracker');
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-fleet-activity-tracker.sidebar.php', 'package.sidebar');
        $this->registerPermissions(__DIR__ . '/Config/seat-fleet-activity-tracker.permissions.php', 'seat-fleet-activity-tracker');
        
    }

    public function boot()
    {
        $this->addCommands();

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'seat-fleet-activity-tracker');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

    }

    private function addCommands() 
    {
        $this->commands([
            PullFleetMembers::class,
        ]);
    }

    /**
     * Get the package's routes.
     *
     * @return string
     */
    protected function getRouteFile()
    {
        return __DIR__.'/routes.php';
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