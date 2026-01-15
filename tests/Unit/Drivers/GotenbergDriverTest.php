<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Drivers\GotenbergDriver;

beforeEach(function () {
    $this->driver = new GotenbergDriver;
});

it('can be instantiated', function () {
    expect($this->driver)->toBeInstanceOf(GotenbergDriver::class);
});

it('returns correct name', function () {
    expect($this->driver->getName())->toBe('gotenberg');
});

it('supports multiple formats', function () {
    $supports = $this->driver->supports();

    expect($supports)->toContain('html');
    expect($supports)->toContain('docx');
    expect($supports)->toContain('xlsx');
    expect($supports)->toContain('pptx');
    expect($supports)->toContain('odt');
    expect($supports)->toContain('markdown');
});

it('returns config array', function () {
    $config = $this->driver->getConfig();

    expect($config)->toBeArray();
});

it('is not available without gotenberg package', function () {
    // The Gotenberg package is not installed in dev dependencies
    // so isAvailable should return false
    if (! class_exists(\Gotenberg\Gotenberg::class)) {
        expect($this->driver->isAvailable())->toBeFalse();
    } else {
        // If package is installed, availability depends on service accessibility
        expect($this->driver->isAvailable())->toBeBool();
    }
});
