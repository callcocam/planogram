<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Callcocam\Planogram\Commands\PlanogramCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

class PlanogramServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('planogram')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations(
                'create_planograms_table',
                'create_stores_table',
                'create_gondolas_table',
                'create_images_table',
                'create_sections_table',
                'create_shelves_table',
                'create_segments_table',
                'create_categories_table',
                'create_products_table',
                'create_layers_table'
            )
            ->hasRoutes('web', 'api')
            ->hasCommand(PlanogramCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->publish('planogram:translations')
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('callcocam/planogram');
            });
    }
}
