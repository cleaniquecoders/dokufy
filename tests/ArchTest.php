<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('contracts should be interfaces')
    ->expect('CleaniqueCoders\Dokufy\Contracts')
    ->toBeInterfaces();

arch('exceptions should extend base exception')
    ->expect('CleaniqueCoders\Dokufy\Exceptions')
    ->toExtend(\Exception::class);

arch('drivers should implement Driver contract')
    ->expect('CleaniqueCoders\Dokufy\Drivers')
    ->toImplement(\CleaniqueCoders\Dokufy\Contracts\Driver::class);

arch('service provider should extend PackageServiceProvider')
    ->expect('CleaniqueCoders\Dokufy\DokufyServiceProvider')
    ->toExtend(\Spatie\LaravelPackageTools\PackageServiceProvider::class);

arch('facades should extend base Facade')
    ->expect('CleaniqueCoders\Dokufy\Facades')
    ->toExtend(\Illuminate\Support\Facades\Facade::class);

arch('traits should be traits')
    ->expect('CleaniqueCoders\Dokufy\Concerns')
    ->toBeTraits();

arch('all classes should use strict types')
    ->expect('CleaniqueCoders\Dokufy')
    ->toUseStrictTypes();

arch('source files should not depend on test classes')
    ->expect('CleaniqueCoders\Dokufy')
    ->not->toUse(['PHPUnit\Framework\TestCase', 'Tests'])
    ->ignoring(['CleaniqueCoders\Dokufy\Drivers\FakeDriver']);
