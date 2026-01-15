<?php

namespace CleaniqueCoders\Dokufy;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\Dokufy\Commands\DokufyCommand;

class DokufyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('dokufy')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_dokufy_table')
            ->hasCommand(DokufyCommand::class);
    }
}
