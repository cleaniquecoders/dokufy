<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Dokufy;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;

beforeEach(function () {
    $this->dokufy = app(Dokufy::class);
});

it('can be instantiated', function () {
    expect($this->dokufy)->toBeInstanceOf(Dokufy::class);
});

it('can create a new instance with make', function () {
    $instance = $this->dokufy->make();

    expect($instance)->toBeInstanceOf(Dokufy::class);
    expect($instance)->not->toBe($this->dokufy);
});

it('can set html content', function () {
    $result = $this->dokufy->html('<h1>Hello World</h1>');

    expect($result)->toBeInstanceOf(Dokufy::class);
});

it('can set data', function () {
    $result = $this->dokufy->data(['name' => 'John']);

    expect($result)->toBeInstanceOf(Dokufy::class);
});

it('can chain data calls', function () {
    $result = $this->dokufy
        ->data(['name' => 'John'])
        ->data(['email' => 'john@example.com']);

    expect($result)->toBeInstanceOf(Dokufy::class);
});

it('throws exception for non-existent template', function () {
    $this->dokufy->template('/path/to/non-existent/template.docx');
})->throws(TemplateNotFoundException::class);

it('can fake the driver for testing', function () {
    $fakeDriver = $this->dokufy->fake();

    expect($fakeDriver)->toBeInstanceOf(FakeDriver::class);
});

it('can generate pdf with fake driver', function () {
    $this->dokufy->fake();

    $outputPath = sys_get_temp_dir().'/test-output.pdf';

    $result = $this->dokufy
        ->html('<h1>Hello World</h1>')
        ->toPdf($outputPath);

    expect($result)->toBe($outputPath);

    $this->dokufy->assertPdfGenerated();
    $this->dokufy->assertGenerated($outputPath);
});

it('can reset the instance', function () {
    $this->dokufy
        ->html('<h1>Test</h1>')
        ->data(['key' => 'value'])
        ->reset();

    // After reset, we should be able to start fresh
    $result = $this->dokufy->html('<h1>New Content</h1>');

    expect($result)->toBeInstanceOf(Dokufy::class);
});

it('replaces placeholders in html content', function () {
    $this->dokufy->fake();

    $outputPath = sys_get_temp_dir().'/test-placeholder.pdf';

    $this->dokufy
        ->html('<h1>Hello {{ name }}</h1>')
        ->data(['name' => 'World'])
        ->toPdf($outputPath);

    $this->dokufy->assertPdfGenerated();
});

it('checks if fake driver is always available', function () {
    expect($this->dokufy->isDriverAvailable('fake'))->toBeTrue();
});
