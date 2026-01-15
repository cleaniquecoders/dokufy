<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Dokufy;
use CleaniqueCoders\Dokufy\Drivers\ChromiumDriver;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Drivers\GotenbergDriver;
use CleaniqueCoders\Dokufy\Drivers\LibreOfficeDriver;
use CleaniqueCoders\Dokufy\Drivers\PhpWordDriver;

describe('ServiceProvider', function () {
    it('registers Dokufy as singleton', function () {
        $instance1 = app(Dokufy::class);
        $instance2 = app(Dokufy::class);

        expect($instance1)->toBe($instance2);
    });

    it('registers Dokufy with alias', function () {
        $instance = app('dokufy');

        expect($instance)->toBeInstanceOf(Dokufy::class);
    });

    it('registers fake driver', function () {
        $driver = app('dokufy.driver.fake');

        expect($driver)->toBeInstanceOf(FakeDriver::class);
        expect($driver)->toBeInstanceOf(Driver::class);
    });

    it('registers gotenberg driver', function () {
        $driver = app('dokufy.driver.gotenberg');

        expect($driver)->toBeInstanceOf(GotenbergDriver::class);
        expect($driver)->toBeInstanceOf(Driver::class);
    });

    it('registers libreoffice driver', function () {
        $driver = app('dokufy.driver.libreoffice');

        expect($driver)->toBeInstanceOf(LibreOfficeDriver::class);
        expect($driver)->toBeInstanceOf(Driver::class);
    });

    it('registers chromium driver', function () {
        $driver = app('dokufy.driver.chromium');

        expect($driver)->toBeInstanceOf(ChromiumDriver::class);
        expect($driver)->toBeInstanceOf(Driver::class);
    });

    it('registers phpword driver', function () {
        $driver = app('dokufy.driver.phpword');

        expect($driver)->toBeInstanceOf(PhpWordDriver::class);
        expect($driver)->toBeInstanceOf(Driver::class);
    });

    it('registers drivers as singletons', function () {
        $driver1 = app('dokufy.driver.fake');
        $driver2 = app('dokufy.driver.fake');

        expect($driver1)->toBe($driver2);
    });

    it('publishes config file', function () {
        expect(config('dokufy'))->toBeArray();
        expect(config('dokufy.default'))->not->toBeNull();
    });

    it('has default driver configuration', function () {
        expect(config('dokufy.default'))->toBeString();
    });

    it('has drivers configuration', function () {
        expect(config('dokufy.drivers'))->toBeArray();
    });
});
