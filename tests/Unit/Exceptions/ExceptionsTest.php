<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DokufyException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;

describe('DokufyException', function () {
    it('extends base Exception class', function () {
        $exception = new DokufyException('Test message');

        expect($exception)->toBeInstanceOf(\Exception::class);
        expect($exception->getMessage())->toBe('Test message');
    });
});

describe('DriverException', function () {
    it('extends DokufyException', function () {
        $exception = DriverException::notFound('test');

        expect($exception)->toBeInstanceOf(DokufyException::class);
    });

    it('creates notFound exception with correct message', function () {
        $exception = DriverException::notFound('gotenberg');

        expect($exception->getMessage())->toBe('Driver [gotenberg] not found.');
    });

    it('creates notAvailable exception with correct message', function () {
        $exception = DriverException::notAvailable('libreoffice');

        expect($exception->getMessage())->toBe('Driver [libreoffice] is not available. Please check your configuration.');
    });

    it('creates notConfigured exception with correct message', function () {
        $exception = DriverException::notConfigured('chromium');

        expect($exception->getMessage())->toBe('Driver [chromium] is not properly configured.');
    });
});

describe('ConversionException', function () {
    it('extends DokufyException', function () {
        $exception = ConversionException::failed('test error');

        expect($exception)->toBeInstanceOf(DokufyException::class);
    });

    it('creates failed exception with correct message', function () {
        $exception = ConversionException::failed('Connection timeout');

        expect($exception->getMessage())->toBe('Document conversion failed: Connection timeout');
    });

    it('creates unsupportedFormat exception with correct message', function () {
        $exception = ConversionException::unsupportedFormat('xyz');

        expect($exception->getMessage())->toBe('Unsupported format: xyz');
    });

    it('creates outputFailed exception with correct message', function () {
        $exception = ConversionException::outputFailed('/tmp/output.pdf');

        expect($exception->getMessage())->toBe('Failed to write output to: /tmp/output.pdf');
    });
});

describe('TemplateNotFoundException', function () {
    it('extends DokufyException', function () {
        $exception = TemplateNotFoundException::atPath('/path/to/file.docx');

        expect($exception)->toBeInstanceOf(DokufyException::class);
    });

    it('creates atPath exception with correct message', function () {
        $exception = TemplateNotFoundException::atPath('/templates/missing.docx');

        expect($exception->getMessage())->toBe('Template not found at: /templates/missing.docx');
    });
});
