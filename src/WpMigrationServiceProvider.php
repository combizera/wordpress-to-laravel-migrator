<?php

namespace Combizera\WpMigration;

use Combizera\WpMigration\Console\MigrateWpXmlCommand;
use Illuminate\Support\ServiceProvider;

class WpMigrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MigrateWpXmlCommand::class,
        ]);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? null;

            if ($command === 'wp:migrate') {
                \Log::info('WpMigrationServiceProvider running 🔥');
            }
        }
    }
}
