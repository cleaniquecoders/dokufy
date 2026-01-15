<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Drivers\LibreOfficeDriver;

beforeEach(function () {
    $this->driver = new LibreOfficeDriver;
});

it('can be instantiated', function () {
    expect($this->driver)->toBeInstanceOf(LibreOfficeDriver::class);
});

it('returns correct name', function () {
    expect($this->driver->getName())->toBe('libreoffice');
});

it('supports multiple formats', function () {
    $supports = $this->driver->supports();

    expect($supports)->toContain('html');
    expect($supports)->toContain('docx');
    expect($supports)->toContain('xlsx');
    expect($supports)->toContain('pptx');
    expect($supports)->toContain('odt');
});

it('returns config array', function () {
    $config = $this->driver->getConfig();

    expect($config)->toBeArray();
});

it('checks availability based on binary presence', function () {
    // This will return true or false depending on whether LibreOffice is installed
    expect($this->driver->isAvailable())->toBeBool();
});
