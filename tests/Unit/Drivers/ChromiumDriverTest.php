<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Drivers\ChromiumDriver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

beforeEach(function () {
    $this->driver = new ChromiumDriver;
});

it('can be instantiated', function () {
    expect($this->driver)->toBeInstanceOf(ChromiumDriver::class);
});

it('returns correct name', function () {
    expect($this->driver->getName())->toBe('chromium');
});

it('only supports html format', function () {
    $supports = $this->driver->supports();

    expect($supports)->toBe(['html']);
});

it('returns config array', function () {
    $config = $this->driver->getConfig();

    expect($config)->toBeArray();
});

it('is not available without browsershot package', function () {
    // The Browsershot package is not installed in dev dependencies
    // so isAvailable should return false
    if (! class_exists(\Spatie\Browsershot\Browsershot::class)) {
        expect($this->driver->isAvailable())->toBeFalse();
    } else {
        expect($this->driver->isAvailable())->toBeTrue();
    }
});

it('throws exception for docx conversion', function () {
    $this->driver->docxToPdf('/tmp/test.docx', '/tmp/test.pdf');
})->throws(ConversionException::class);
