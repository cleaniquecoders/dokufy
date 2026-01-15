<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\PhpWordDriver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

beforeEach(function () {
    $this->driver = new PhpWordDriver;
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->driver)->toBeInstanceOf(PhpWordDriver::class);
    });

    it('implements Driver contract', function () {
        expect($this->driver)->toBeInstanceOf(Driver::class);
    });
});

describe('driver info', function () {
    it('returns correct name', function () {
        expect($this->driver->getName())->toBe('phpword');
    });

    it('returns config array', function () {
        $config = $this->driver->getConfig();

        expect($config)->toBeArray();
    });
});

describe('supported formats', function () {
    it('supports docx format', function () {
        $supports = $this->driver->supports();

        expect($supports)->toContain('docx');
    });

    it('returns array of supported formats', function () {
        expect($this->driver->supports())->toBeArray();
    });

    it('supports only 1 format', function () {
        expect($this->driver->supports())->toHaveCount(1);
    });

    it('does not support html', function () {
        // PhpWordDriver officially only supports docx format
        expect($this->driver->supports())->not->toContain('html');
    });
});

describe('availability', function () {
    it('is not available without phpword package', function () {
        if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
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
        if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');
        } else {
            expect(true)->toBeTrue();
        }
    })->throws(DriverException::class)->skip(class_exists(\PhpOffice\PhpWord\PhpWord::class));
});

describe('docx to pdf conversion', function () {
    it('throws driver exception when package not available', function () {
        if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            $this->driver->docxToPdf('/tmp/test.docx', '/tmp/test.pdf');
        } else {
            expect(true)->toBeTrue();
        }
    })->throws(DriverException::class)->skip(class_exists(\PhpOffice\PhpWord\PhpWord::class));

    it('throws exception for non-existent source file', function () {
        if (class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            try {
                $this->driver->docxToPdf('/tmp/non-existent.docx', '/tmp/test.pdf');
            } catch (ConversionException $e) {
                expect($e->getMessage())->toContain('Source file not found');
            }
        } else {
            // If PHPWord is not installed, skip this test
            expect(true)->toBeTrue();
        }
    })->skip(! class_exists(\PhpOffice\PhpWord\PhpWord::class));
});
