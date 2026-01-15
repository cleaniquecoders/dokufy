# Fake Driver

The Fake driver is an in-memory testing driver that records method calls and
returns predictable outputs. Use it in unit tests to verify document generation
without actual file operations.

## Purpose

- Test document generation logic without external dependencies
- Assert that documents were generated with correct parameters
- Avoid slow I/O operations in tests
- Work in CI/CD environments without Docker or LibreOffice

## Installation

No additional packages required. The Fake driver is included with Dokufy.

## Usage

### Basic Testing

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

it('generates an offer letter', function () {
    // Arrange
    Dokufy::fake();
    $employee = Employee::factory()->create();

    // Act
    app(GenerateOfferLetter::class)->execute($employee);

    // Assert
    Dokufy::assertPdfGenerated();
});
```

### Asserting Specific Output Path

```php
it('saves document to correct path', function () {
    Dokufy::fake();

    $invoice = Invoice::factory()->create(['number' => 'INV-001']);

    app(GenerateInvoice::class)->execute($invoice);

    Dokufy::assertGenerated(storage_path('invoices/INV-001.pdf'));
});
```

### Asserting DOCX Generation

```php
it('generates DOCX output', function () {
    Dokufy::fake();

    app(GenerateContract::class)->asDocx();

    Dokufy::assertDocxGenerated();
});
```

## Available Assertions

| Method | Description |
|--------|-------------|
| `assertPdfGenerated()` | Assert any PDF was generated |
| `assertDocxGenerated()` | Assert any DOCX was generated |
| `assertGenerated($path)` | Assert document generated at specific path |
| `assertNotGenerated($path)` | Assert document was NOT generated at path |
| `assertTemplateUsed($path)` | Assert specific template was loaded |
| `assertDataContains($key, $value)` | Assert data array contains key/value |
| `assertGeneratedCount($count)` | Assert specific number of documents generated |

## Inspecting Generated Documents

Access details about what was generated:

```php
it('uses correct template and data', function () {
    Dokufy::fake();

    app(GenerateReport::class)->execute([
        'title' => 'Monthly Report',
        'month' => 'January',
    ]);

    $fake = Dokufy::getFake();

    // Check template used
    expect($fake->getLastTemplate())
        ->toBe(resource_path('templates/report.docx'));

    // Check data passed
    expect($fake->getLastData())
        ->toHaveKey('title', 'Monthly Report')
        ->toHaveKey('month', 'January');

    // Check output path
    expect($fake->getLastOutputPath())
        ->toContain('reports/');
});
```

## Testing Multiple Documents

```php
it('generates multiple invoices', function () {
    Dokufy::fake();

    $invoices = Invoice::factory()->count(3)->create();

    foreach ($invoices as $invoice) {
        app(GenerateInvoice::class)->execute($invoice);
    }

    Dokufy::assertGeneratedCount(3);

    // Check each was generated
    foreach ($invoices as $invoice) {
        Dokufy::assertGenerated(storage_path("invoices/{$invoice->number}.pdf"));
    }
});
```

## Testing Driver Selection

```php
it('uses the specified driver', function () {
    Dokufy::fake();

    Dokufy::driver('gotenberg')
        ->template(resource_path('templates/doc.docx'))
        ->data(['name' => 'Test'])
        ->toPdf(storage_path('output.pdf'));

    $fake = Dokufy::getFake();
    expect($fake->getDriverUsed())->toBe('gotenberg');
});
```

## Testing Error Handling

Configure the Fake driver to throw exceptions:

```php
it('handles conversion failures gracefully', function () {
    Dokufy::fake()->shouldFail('Conversion failed');

    expect(fn () => app(GenerateReport::class)->execute())
        ->toThrow(ConversionException::class, 'Conversion failed');
});
```

## Testing Without Facade

For testing classes that inject Dokufy:

```php
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Dokufy;

it('works with dependency injection', function () {
    $fake = new FakeDriver;
    $dokufy = new Dokufy($fake);

    $this->app->instance(Dokufy::class, $dokufy);

    // Your test code...

    expect($fake->wasGenerated())->toBeTrue();
});
```

## Complete Test Example

```php
<?php

use CleaniqueCoders\Dokufy\Facades\Dokufy;
use App\Models\Employee;
use App\Services\OfferLetterGenerator;

beforeEach(function () {
    Dokufy::fake();
});

describe('OfferLetterGenerator', function () {
    it('generates PDF offer letter', function () {
        $employee = Employee::factory()->create([
            'name' => 'John Doe',
            'position' => 'Developer',
            'salary' => 5000,
        ]);

        app(OfferLetterGenerator::class)->execute($employee);

        Dokufy::assertPdfGenerated();
        Dokufy::assertGenerated(storage_path("offers/{$employee->id}.pdf"));
    });

    it('uses correct template', function () {
        $employee = Employee::factory()->create();

        app(OfferLetterGenerator::class)->execute($employee);

        Dokufy::assertTemplateUsed(resource_path('templates/offer-letter.docx'));
    });

    it('passes employee data to template', function () {
        $employee = Employee::factory()->create([
            'name' => 'Jane Smith',
            'position' => 'Manager',
        ]);

        app(OfferLetterGenerator::class)->execute($employee);

        Dokufy::assertDataContains('name', 'Jane Smith');
        Dokufy::assertDataContains('position', 'Manager');
    });

    it('handles missing template gracefully', function () {
        Dokufy::fake()->shouldFailWithMissingTemplate();

        $employee = Employee::factory()->create();

        expect(fn () => app(OfferLetterGenerator::class)->execute($employee))
            ->toThrow(TemplateNotFoundException::class);
    });
});
```

## Resetting Between Tests

The Fake driver automatically resets between tests when using `Dokufy::fake()`. For manual reset:

```php
afterEach(function () {
    Dokufy::clearFake();
});
```

## Next Steps

- [Testing](../07-testing/README.md) - Complete testing guide
- [Examples](../06-examples/README.md) - Real-world examples
- [API Reference](../04-api/README.md) - All available methods
