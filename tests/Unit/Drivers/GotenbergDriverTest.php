<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\GotenbergDriver;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

beforeEach(function () {
    $this->driver = new GotenbergDriver;
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->driver)->toBeInstanceOf(GotenbergDriver::class);
    });

    it('implements Driver contract', function () {
        expect($this->driver)->toBeInstanceOf(Driver::class);
    });
});

describe('driver info', function () {
    it('returns correct name', function () {
        expect($this->driver->getName())->toBe('gotenberg');
    });

    it('returns config array', function () {
        $config = $this->driver->getConfig();

        expect($config)->toBeArray();
    });
});

describe('supported formats', function () {
    it('supports multiple formats', function () {
        $supports = $this->driver->supports();

        expect($supports)->toContain('html');
        expect($supports)->toContain('docx');
        expect($supports)->toContain('xlsx');
        expect($supports)->toContain('pptx');
        expect($supports)->toContain('odt');
        expect($supports)->toContain('markdown');
    });

    it('returns array of supported formats', function () {
        expect($this->driver->supports())->toBeArray();
    });

    it('supports 6 different formats', function () {
        expect($this->driver->supports())->toHaveCount(6);
    });
});

describe('availability', function () {
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

    it('returns boolean for availability check', function () {
        expect($this->driver->isAvailable())->toBeBool();
    });
});

describe('html to pdf conversion', function () {
    it('throws driver exception when package not available', function () {
        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');
        } else {
            // Skip if package is available
            expect(true)->toBeTrue();
        }
    })->throws(DriverException::class)->skip(class_exists(\Gotenberg\Gotenberg::class));
});

describe('docx to pdf conversion', function () {
    it('throws driver exception when package not available', function () {
        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            $this->driver->docxToPdf('/tmp/test.docx', '/tmp/test.pdf');
        } else {
            // Skip if package is available
            expect(true)->toBeTrue();
        }
    })->throws(DriverException::class)->skip(class_exists(\Gotenberg\Gotenberg::class));
});
