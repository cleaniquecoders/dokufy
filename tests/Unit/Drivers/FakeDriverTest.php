<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;

beforeEach(function () {
    $this->driver = new FakeDriver;
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->driver)->toBeInstanceOf(FakeDriver::class);
    });

    it('implements Driver contract', function () {
        expect($this->driver)->toBeInstanceOf(Driver::class);
    });
});

describe('driver info', function () {
    it('returns correct name', function () {
        expect($this->driver->getName())->toBe('fake');
    });

    it('is always available', function () {
        expect($this->driver->isAvailable())->toBeTrue();
    });

    it('returns config array', function () {
        $config = $this->driver->getConfig();

        expect($config)->toBeArray();
    });
});

describe('supported formats', function () {
    it('supports all formats', function () {
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
});

describe('html to pdf conversion', function () {
    it('can convert html to pdf', function () {
        $outputPath = '/tmp/test.pdf';

        $result = $this->driver->htmlToPdf('<h1>Test</h1>', $outputPath);

        expect($result)->toBe($outputPath);
    });

    it('returns output path after conversion', function () {
        $outputPath = '/tmp/output-test.pdf';

        $result = $this->driver->htmlToPdf('<p>Content</p>', $outputPath);

        expect($result)->toBe($outputPath);
    });

    it('records html to pdf call', function () {
        $html = '<h1>Test</h1>';
        $outputPath = '/tmp/test.pdf';

        $this->driver->htmlToPdf($html, $outputPath);

        $calls = $this->driver->getCalls();
        expect($calls)->toHaveCount(1);
        expect($calls[0]['method'])->toBe('htmlToPdf');
        expect($calls[0]['args'])->toBe([$html, $outputPath]);
    });
});

describe('docx to pdf conversion', function () {
    it('can convert docx to pdf', function () {
        $docxPath = '/tmp/test.docx';
        $outputPath = '/tmp/test.pdf';

        $result = $this->driver->docxToPdf($docxPath, $outputPath);

        expect($result)->toBe($outputPath);
    });

    it('records docx to pdf call', function () {
        $docxPath = '/tmp/test.docx';
        $outputPath = '/tmp/test.pdf';

        $this->driver->docxToPdf($docxPath, $outputPath);

        $calls = $this->driver->getCalls();
        expect($calls)->toHaveCount(1);
        expect($calls[0]['method'])->toBe('docxToPdf');
        expect($calls[0]['args'])->toBe([$docxPath, $outputPath]);
    });
});

describe('call recording', function () {
    it('records method calls', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

        $calls = $this->driver->getCalls();

        expect($calls)->toHaveCount(1);
        expect($calls[0]['method'])->toBe('htmlToPdf');
    });

    it('records multiple method calls', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test1.pdf');
        $this->driver->docxToPdf('/tmp/doc.docx', '/tmp/test2.pdf');
        $this->driver->htmlToPdf('<h2>Another</h2>', '/tmp/test3.pdf');

        $calls = $this->driver->getCalls();

        expect($calls)->toHaveCount(3);
    });

    it('stores call arguments', function () {
        $html = '<h1>Specific Test</h1>';
        $outputPath = '/tmp/specific-test.pdf';

        $this->driver->htmlToPdf($html, $outputPath);

        $calls = $this->driver->getCalls();
        expect($calls[0]['args'][0])->toBe($html);
        expect($calls[0]['args'][1])->toBe($outputPath);
    });
});

describe('file tracking', function () {
    it('tracks generated files', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test1.pdf');
        $this->driver->docxToPdf('/tmp/doc.docx', '/tmp/test2.pdf');

        $files = $this->driver->getGeneratedFiles();

        expect($files)->toHaveCount(2);
        expect($files)->toContain('/tmp/test1.pdf');
        expect($files)->toContain('/tmp/test2.pdf');
    });

    it('returns empty array when no files generated', function () {
        expect($this->driver->getGeneratedFiles())->toBeEmpty();
    });

    it('tracks files in order of generation', function () {
        $this->driver->htmlToPdf('<h1>First</h1>', '/tmp/first.pdf');
        $this->driver->htmlToPdf('<h1>Second</h1>', '/tmp/second.pdf');
        $this->driver->htmlToPdf('<h1>Third</h1>', '/tmp/third.pdf');

        $files = $this->driver->getGeneratedFiles();

        expect($files[0])->toBe('/tmp/first.pdf');
        expect($files[1])->toBe('/tmp/second.pdf');
        expect($files[2])->toBe('/tmp/third.pdf');
    });
});

describe('assertions', function () {
    it('can assert file was generated', function () {
        $outputPath = '/tmp/test.pdf';

        $this->driver->htmlToPdf('<h1>Test</h1>', $outputPath);

        $this->driver->assertGenerated($outputPath);

        expect($this->driver->getGeneratedFiles())->toContain($outputPath);
    });

    it('can assert pdf was generated', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

        $this->driver->assertPdfGenerated();

        expect(true)->toBeTrue();
    });

    it('can assert docx was generated', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.docx');

        $this->driver->assertDocxGenerated();

        expect(true)->toBeTrue();
    });

    it('can assert nothing generated', function () {
        $this->driver->assertNothingGenerated();

        expect($this->driver->getGeneratedFiles())->toBeEmpty();
    });

    it('can assert method was called', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

        $this->driver->assertMethodCalled('htmlToPdf');

        expect($this->driver->getCalls())->toHaveCount(1);
    });

    it('can assert method was called with specific args', function () {
        $html = '<h1>Test</h1>';
        $outputPath = '/tmp/test.pdf';

        $this->driver->htmlToPdf($html, $outputPath);

        $this->driver->assertMethodCalled('htmlToPdf', [$html, $outputPath]);

        expect(true)->toBeTrue();
    });
});

describe('reset', function () {
    it('can reset recorded data', function () {
        $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

        expect($this->driver->getCalls())->toHaveCount(1);
        expect($this->driver->getGeneratedFiles())->toHaveCount(1);

        $this->driver->reset();

        expect($this->driver->getCalls())->toHaveCount(0);
        expect($this->driver->getGeneratedFiles())->toHaveCount(0);
    });

    it('allows new recordings after reset', function () {
        $this->driver->htmlToPdf('<h1>First</h1>', '/tmp/first.pdf');
        $this->driver->reset();
        $this->driver->htmlToPdf('<h1>Second</h1>', '/tmp/second.pdf');

        expect($this->driver->getCalls())->toHaveCount(1);
        expect($this->driver->getGeneratedFiles())->toHaveCount(1);
    });
});
