<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\LibreOfficeDriver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

beforeEach(function () {
    $this->driver = new LibreOfficeDriver;
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->driver)->toBeInstanceOf(LibreOfficeDriver::class);
    });

    it('implements Driver contract', function () {
        expect($this->driver)->toBeInstanceOf(Driver::class);
    });
});

describe('driver info', function () {
    it('returns correct name', function () {
        expect($this->driver->getName())->toBe('libreoffice');
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
    });

    it('returns array of supported formats', function () {
        expect($this->driver->supports())->toBeArray();
    });

    it('supports 5 different formats', function () {
        expect($this->driver->supports())->toHaveCount(5);
    });

    it('does not support markdown', function () {
        expect($this->driver->supports())->not->toContain('markdown');
    });
});

describe('availability', function () {
    it('checks availability based on binary presence', function () {
        // This will return true or false depending on whether LibreOffice is installed
        expect($this->driver->isAvailable())->toBeBool();
    });

    it('returns boolean for availability check', function () {
        expect($this->driver->isAvailable())->toBeBool();
    });
});

describe('docx to pdf conversion', function () {
    it('throws exception for non-existent source file', function () {
        $this->driver->docxToPdf('/tmp/non-existent-file.docx', '/tmp/test.pdf');
    })->throws(ConversionException::class);

    it('throws exception with correct message for missing file', function () {
        try {
            $this->driver->docxToPdf('/tmp/non-existent.docx', '/tmp/test.pdf');
        } catch (ConversionException $e) {
            expect($e->getMessage())->toContain('Source file not found');
        }
    });
});
