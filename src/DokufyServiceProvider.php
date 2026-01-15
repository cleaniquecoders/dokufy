<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy;

use CleaniqueCoders\Dokufy\Commands\DokufyCommand;
use CleaniqueCoders\Dokufy\Drivers\ChromiumDriver;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Drivers\GotenbergDriver;
use CleaniqueCoders\Dokufy\Drivers\LibreOfficeDriver;
use CleaniqueCoders\Dokufy\Drivers\PhpWordDriver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DokufyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('dokufy')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(DokufyCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->registerDrivers();
        $this->registerDokufy();
    }

    protected function registerDrivers(): void
    {
        // Register Fake Driver
        $this->app->singleton('dokufy.driver.fake', function () {
            return new FakeDriver;
        });

        // Register Gotenberg Driver
        $this->app->singleton('dokufy.driver.gotenberg', function () {
            return new GotenbergDriver;
        });

        // Register LibreOffice Driver
        $this->app->singleton('dokufy.driver.libreoffice', function () {
            return new LibreOfficeDriver;
        });

        // Register Chromium Driver
        $this->app->singleton('dokufy.driver.chromium', function () {
            return new ChromiumDriver;
        });

        // Register PHPWord Driver
        $this->app->singleton('dokufy.driver.phpword', function () {
            return new PhpWordDriver;
        });
    }

    protected function registerDokufy(): void
    {
        $this->app->singleton(Dokufy::class, function ($app) {
            return new Dokufy($app);
        });

        $this->app->alias(Dokufy::class, 'dokufy');
    }
}
