<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Drivers\PhpWordDriver;

beforeEach(function () {
    $this->driver = new PhpWordDriver;
});

it('can be instantiated', function () {
    expect($this->driver)->toBeInstanceOf(PhpWordDriver::class);
});

it('returns correct name', function () {
    expect($this->driver->getName())->toBe('phpword');
});

it('supports docx format', function () {
    $supports = $this->driver->supports();

    expect($supports)->toContain('docx');
});

it('returns config array', function () {
    $config = $this->driver->getConfig();

    expect($config)->toBeArray();
});

it('is not available without phpword package', function () {
    // The PHPWord package is not installed in dev dependencies
    // so isAvailable should return false
    if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
        expect($this->driver->isAvailable())->toBeFalse();
    } else {
        expect($this->driver->isAvailable())->toBeTrue();
    }
});
