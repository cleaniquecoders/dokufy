<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\ChromiumDriver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

beforeEach(function () {
    $this->driver = new ChromiumDriver;
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->driver)->toBeInstanceOf(ChromiumDriver::class);
    });

    it('implements Driver contract', function () {
        expect($this->driver)->toBeInstanceOf(Driver::class);
    });
});

describe('driver info', function () {
    it('returns correct name', function () {
        expect($this->driver->getName())->toBe('chromium');
    });

    it('returns config array', function () {
        $config = $this->driver->getConfig();

        expect($config)->toBeArray();
    });
});

describe('supported formats', function () {
    it('only supports html format', function () {
        $supports = $this->driver->supports();

        expect($supports)->toBe(['html']);
    });

    it('returns array of supported formats', function () {
        expect($this->driver->supports())->toBeArray();
    });

    it('supports only 1 format', function () {
        expect($this->driver->supports())->toHaveCount(1);
    });

    it('does not support docx', function () {
        expect($this->driver->supports())->not->toContain('docx');
    });

    it('does not support xlsx', function () {
        expect($this->driver->supports())->not->toContain('xlsx');
    });
});

describe('availability', function () {
    it('is not available without browsershot package', function () {
        if (! class_exists(\Spatie\Browsershot\Browsershot::class)) {
            expect($this->driver->isAvailable())->toBeFalse();
        } else {
            expect($this->driver->isAvailable())->toBeTrue();
        }
    });

    it('returns boolean for availability check', function () {
        expect($this->driver->isAvailable())->toBeBool();
    });
});

describe('html to pdf conversion', function () {
    it('throws driver exception when package not available', function () {
        if (! class_exists(\Spatie\Browsershot\Browsershot::class)) {
            $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');
        } else {
            expect(true)->toBeTrue();
        }
    })->throws(DriverException::class)->skip(class_exists(\Spatie\Browsershot\Browsershot::class));
});

describe('docx to pdf conversion', function () {
    it('throws exception for docx conversion', function () {
        $this->driver->docxToPdf('/tmp/test.docx', '/tmp/test.pdf');
    })->throws(ConversionException::class);

    it('throws unsupported format exception', function () {
        try {
            $this->driver->docxToPdf('/tmp/test.docx', '/tmp/test.pdf');
        } catch (ConversionException $e) {
            expect($e->getMessage())->toContain('docx');
        }
    });
});
