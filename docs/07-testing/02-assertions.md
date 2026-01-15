# Assertions Reference

Complete reference for all Dokufy testing assertions.

## Enabling Test Mode

Before using assertions, enable the fake driver:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::fake();
```

## Document Generation Assertions

### assertPdfGenerated()

Assert that any PDF was generated during the test.

```php
Dokufy::fake();

// Code that generates PDF

Dokufy::assertPdfGenerated();
```

**Fails if**: No PDF was generated.

### assertDocxGenerated()

Assert that any DOCX was generated during the test.

```php
Dokufy::fake();

// Code that generates DOCX

Dokufy::assertDocxGenerated();
```

**Fails if**: No DOCX was generated.

### assertGenerated()

Assert that a document was generated at a specific path.

```php
Dokufy::fake();

$path = storage_path('invoices/INV-001.pdf');
Dokufy::template($template)->data($data)->toPdf($path);

Dokufy::assertGenerated($path);
```

**Fails if**: No document was generated at the specified path.

### assertNotGenerated()

Assert that a document was NOT generated at a specific path.

```php
Dokufy::fake();

// Code that might or might not generate a document

Dokufy::assertNotGenerated(storage_path('should-not-exist.pdf'));
```

**Fails if**: A document was generated at the specified path.

### assertGeneratedCount()

Assert the exact number of documents generated.

```php
Dokufy::fake();

foreach ($invoices as $invoice) {
    app(InvoiceGenerator::class)->generate($invoice);
}

Dokufy::assertGeneratedCount(5);
```

**Fails if**: The count doesn't match.

## Template Assertions

### assertTemplateUsed()

Assert that a specific template was loaded.

```php
Dokufy::fake();

app(OfferLetterGenerator::class)->generate($employee);

Dokufy::assertTemplateUsed(resource_path('templates/offer-letter.docx'));
```

**Fails if**: The specified template was not used.

### assertHtmlUsed()

Assert that HTML content was used (not a template file).

```php
Dokufy::fake();

Dokufy::html('<h1>Report</h1>')->toPdf($path);

Dokufy::assertHtmlUsed();
```

**Fails if**: No HTML content was used.

## Data Assertions

### assertDataContains()

Assert that the data array contains a specific key-value pair.

```php
Dokufy::fake();

Dokufy::template($template)
    ->data(['customer_name' => 'John Doe', 'total' => 1500])
    ->toPdf($path);

Dokufy::assertDataContains('customer_name', 'John Doe');
Dokufy::assertDataContains('total', 1500);
```

**Fails if**: The key doesn't exist or the value doesn't match.

### assertDataHasKey()

Assert that the data array contains a specific key.

```php
Dokufy::fake();

Dokufy::template($template)
    ->data(['invoice_number' => 'INV-001'])
    ->toPdf($path);

Dokufy::assertDataHasKey('invoice_number');
```

**Fails if**: The key doesn't exist.

### assertDataDoesntHaveKey()

Assert that the data array does NOT contain a specific key.

```php
Dokufy::fake();

Dokufy::template($template)
    ->data(['name' => 'John'])
    ->toPdf($path);

Dokufy::assertDataDoesntHaveKey('secret_field');
```

**Fails if**: The key exists.

## Driver Assertions

### assertDriverUsed()

Assert that a specific driver was used.

```php
Dokufy::fake();

Dokufy::driver('gotenberg')->html($html)->toPdf($path);

Dokufy::assertDriverUsed('gotenberg');
```

**Fails if**: A different driver was used.

## Inspection Methods

These methods return data for custom assertions.

### getFake()

Get the FakeDriver instance for inspection.

```php
Dokufy::fake();

// Generate documents...

$fake = Dokufy::getFake();
```

### getLastTemplate()

Get the path of the last template used.

```php
$fake = Dokufy::getFake();
$templatePath = $fake->getLastTemplate();

expect($templatePath)->toBe(resource_path('templates/invoice.docx'));
```

### getLastHtml()

Get the last HTML content used.

```php
$fake = Dokufy::getFake();
$html = $fake->getLastHtml();

expect($html)->toContain('<h1>Invoice</h1>');
```

### getLastData()

Get the last data array used.

```php
$fake = Dokufy::getFake();
$data = $fake->getLastData();

expect($data)->toHaveKey('customer_name');
expect($data['total'])->toBe(1500);
```

### getLastOutputPath()

Get the last output path.

```php
$fake = Dokufy::getFake();
$path = $fake->getLastOutputPath();

expect($path)->toContain('invoices/');
```

### getAllGenerations()

Get all document generations.

```php
$fake = Dokufy::getFake();
$generations = $fake->getAllGenerations();

expect($generations)->toHaveCount(3);
expect($generations[0]['type'])->toBe('pdf');
```

## Simulating Failures

### shouldFail()

Configure the fake to throw an exception.

```php
Dokufy::fake()->shouldFail('Conversion failed');

expect(fn () => Dokufy::html($html)->toPdf($path))
    ->toThrow(ConversionException::class, 'Conversion failed');
```

### shouldFailWithMissingTemplate()

Configure the fake to throw TemplateNotFoundException.

```php
Dokufy::fake()->shouldFailWithMissingTemplate();

expect(fn () => Dokufy::template('/missing.docx')->data([])->toPdf($path))
    ->toThrow(TemplateNotFoundException::class);
```

## Resetting State

### clearFake()

Clear the fake driver and reset state.

```php
afterEach(function () {
    Dokufy::clearFake();
});
```

## Chaining Multiple Assertions

```php
it('generates invoice correctly', function () {
    Dokufy::fake();

    $invoice = Invoice::factory()->create(['number' => 'INV-001']);
    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated(storage_path('invoices/INV-001.pdf'));
    Dokufy::assertTemplateUsed(resource_path('templates/invoice.docx'));
    Dokufy::assertDataContains('invoice_number', 'INV-001');
    Dokufy::assertGeneratedCount(1);
});
```

## Next Steps

- [Testing Patterns](03-patterns.md) - Advanced testing patterns
- [Examples](../06-examples/README.md) - Real-world examples
