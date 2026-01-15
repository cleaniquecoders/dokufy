<?php

namespace CleaniqueCoders\Dokufy;

use CleaniqueCoders\Dokufy\Commands\DokufyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
