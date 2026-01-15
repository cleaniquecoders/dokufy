<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Facades\Dokufy;

it('can generate pdf from html using facade', function () {
    Dokufy::fake();

    $outputPath = sys_get_temp_dir().'/facade-test.pdf';

    Dokufy::html('<h1>Hello from Facade</h1>')
        ->toPdf($outputPath);

    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated($outputPath);
});

it('can chain data and html methods', function () {
    Dokufy::fake();

    $outputPath = sys_get_temp_dir().'/chained-test.pdf';

    Dokufy::html('<h1>Hello {{ name }}</h1>')
        ->data(['name' => 'World'])
        ->toPdf($outputPath);

    Dokufy::assertPdfGenerated();
});

it('can use make to create new instances', function () {
    // Make creates a new instance, so we test that it works
    // by checking we can create an instance with the fake driver
    $instance = Dokufy::make('fake');

    $outputPath = sys_get_temp_dir().'/make-test.pdf';

    $result = $instance
        ->html('<h1>New Instance</h1>')
        ->toPdf($outputPath);

    expect($result)->toBe($outputPath);
});

it('can reset and reuse facade', function () {
    Dokufy::fake();

    // First use
    Dokufy::html('<h1>First</h1>')
        ->data(['key' => 'value1'])
        ->toPdf(sys_get_temp_dir().'/first.pdf');

    Dokufy::reset();

    // After reset, need to fake again since reset clears the driver
    Dokufy::fake();

    // Second use
    Dokufy::html('<h1>Second</h1>')
        ->data(['key' => 'value2'])
        ->toPdf(sys_get_temp_dir().'/second.pdf');

    Dokufy::assertPdfGenerated();
});

it('can check driver availability', function () {
    // Fake driver should always be available
    expect(Dokufy::isDriverAvailable('fake'))->toBeTrue();
});

it('returns available drivers list', function () {
    $drivers = Dokufy::getAvailableDrivers();

    expect($drivers)->toBeArray();
    expect($drivers)->toContain('fake');
});

it('can use with method for placeholder handler', function () {
    Dokufy::fake();

    // Create a simple mock handler
    $handler = new class
    {
        /**
         * @return array<string, string>
         */
        public function toArray(): array
        {
            return ['name' => 'Test', 'company' => 'Acme Inc'];
        }
    };

    $outputPath = sys_get_temp_dir().'/handler-test.pdf';

    Dokufy::html('<h1>Hello {{ name }} from {{ company }}</h1>')
        ->with($handler)
        ->toPdf($outputPath);

    Dokufy::assertPdfGenerated();
});
