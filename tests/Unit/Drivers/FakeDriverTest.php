<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Drivers\FakeDriver;

beforeEach(function () {
    $this->driver = new FakeDriver;
});

it('can be instantiated', function () {
    expect($this->driver)->toBeInstanceOf(FakeDriver::class);
});

it('returns correct name', function () {
    expect($this->driver->getName())->toBe('fake');
});

it('is always available', function () {
    expect($this->driver->isAvailable())->toBeTrue();
});

it('supports all formats', function () {
    $supports = $this->driver->supports();

    expect($supports)->toContain('html');
    expect($supports)->toContain('docx');
    expect($supports)->toContain('xlsx');
    expect($supports)->toContain('pptx');
    expect($supports)->toContain('odt');
    expect($supports)->toContain('markdown');
});

it('can convert html to pdf', function () {
    $outputPath = '/tmp/test.pdf';

    $result = $this->driver->htmlToPdf('<h1>Test</h1>', $outputPath);

    expect($result)->toBe($outputPath);
});

it('can convert docx to pdf', function () {
    $docxPath = '/tmp/test.docx';
    $outputPath = '/tmp/test.pdf';

    $result = $this->driver->docxToPdf($docxPath, $outputPath);

    expect($result)->toBe($outputPath);
});

it('records method calls', function () {
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

    $calls = $this->driver->getCalls();

    expect($calls)->toHaveCount(1);
    expect($calls[0]['method'])->toBe('htmlToPdf');
});

it('tracks generated files', function () {
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test1.pdf');
    $this->driver->docxToPdf('/tmp/doc.docx', '/tmp/test2.pdf');

    $files = $this->driver->getGeneratedFiles();

    expect($files)->toHaveCount(2);
    expect($files)->toContain('/tmp/test1.pdf');
    expect($files)->toContain('/tmp/test2.pdf');
});

it('can assert file was generated', function () {
    $outputPath = '/tmp/test.pdf';

    $this->driver->htmlToPdf('<h1>Test</h1>', $outputPath);

    // This assertion should pass without throwing
    $this->driver->assertGenerated($outputPath);

    expect($this->driver->getGeneratedFiles())->toContain($outputPath);
});

it('can assert pdf was generated', function () {
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

    // This assertion should pass without throwing
    $this->driver->assertPdfGenerated();

    expect(true)->toBeTrue();
});

it('can assert docx was generated', function () {
    // Generate a docx file
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.docx');

    $this->driver->assertDocxGenerated();

    expect(true)->toBeTrue();
});

it('can assert nothing generated', function () {
    // On a fresh driver, nothing has been generated
    $this->driver->assertNothingGenerated();

    expect($this->driver->getGeneratedFiles())->toBeEmpty();
});

it('can reset recorded data', function () {
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

    expect($this->driver->getCalls())->toHaveCount(1);
    expect($this->driver->getGeneratedFiles())->toHaveCount(1);

    $this->driver->reset();

    expect($this->driver->getCalls())->toHaveCount(0);
    expect($this->driver->getGeneratedFiles())->toHaveCount(0);
});

it('can assert method was called', function () {
    $this->driver->htmlToPdf('<h1>Test</h1>', '/tmp/test.pdf');

    $this->driver->assertMethodCalled('htmlToPdf');

    expect($this->driver->getCalls())->toHaveCount(1);
});

it('returns empty config', function () {
    $config = $this->driver->getConfig();

    expect($config)->toBeArray();
});
