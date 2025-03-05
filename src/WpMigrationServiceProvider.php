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

        $this->publishes([
            __DIR__ . '/../config/wp-migration.php' => config_path('wp-migration.php'),
        ], 'wp-migration-config');
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp-migration.php',
            'wp-migration'
        );

        if ($this->app->runningInConsole()) {
            $command = $_SERVER['argv'][1] ?? null;

            if ($command === 'wp:migrate') {
                \Log::info('WpMigrationServiceProvider running 🔥');
            }
        }
    }
}
