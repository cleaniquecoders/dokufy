# Testing Patterns

Advanced testing patterns and best practices for Dokufy.

## Pattern 1: Testing Document Content

Verify the content passed to document generation:

```php
it('includes all invoice items in PDF', function () {
    Dokufy::fake();

    $invoice = Invoice::factory()
        ->has(InvoiceItem::factory()->count(3)->sequence(
            ['description' => 'Widget A'],
            ['description' => 'Widget B'],
            ['description' => 'Widget C'],
        ))
        ->create();

    app(InvoiceGenerator::class)->generate($invoice);

    $fake = Dokufy::getFake();
    $html = $fake->getLastHtml();

    expect($html)
        ->toContain('Widget A')
        ->toContain('Widget B')
        ->toContain('Widget C');
});
```

## Pattern 2: Testing Conditional Logic

Test different document generation paths:

```php
describe('OfferLetterGenerator', function () {
    beforeEach(fn () => Dokufy::fake());

    it('uses standard template for regular employees', function () {
        $employee = Employee::factory()->create(['type' => 'regular']);

        app(OfferLetterGenerator::class)->generate($employee);

        Dokufy::assertTemplateUsed(resource_path('templates/offer-letter.docx'));
    });

    it('uses executive template for executives', function () {
        $employee = Employee::factory()->create(['type' => 'executive']);

        app(OfferLetterGenerator::class)->generate($employee);

        Dokufy::assertTemplateUsed(resource_path('templates/offer-letter-executive.docx'));
    });

    it('uses contractor template for contractors', function () {
        $employee = Employee::factory()->create(['type' => 'contractor']);

        app(OfferLetterGenerator::class)->generate($employee);

        Dokufy::assertTemplateUsed(resource_path('templates/contractor-agreement.docx'));
    });
});
```

## Pattern 3: Testing Error Handling

Test how your code handles generation failures:

```php
it('logs error when PDF generation fails', function () {
    Dokufy::fake()->shouldFail('Conversion failed');
    Log::spy();

    $invoice = Invoice::factory()->create();

    try {
        app(InvoiceGenerator::class)->generate($invoice);
    } catch (ConversionException $e) {
        // Expected
    }

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($msg) => str_contains($msg, 'PDF generation failed'));
});

it('updates invoice status on generation failure', function () {
    Dokufy::fake()->shouldFail('Service unavailable');

    $invoice = Invoice::factory()->create(['pdf_status' => 'pending']);

    try {
        app(InvoiceGenerator::class)->generate($invoice);
    } catch (ConversionException $e) {
        // Expected
    }

    expect($invoice->fresh()->pdf_status)->toBe('failed');
});
```

## Pattern 4: Testing Retries

Test retry logic with driver failures:

```php
it('retries generation on transient failure', function () {
    $fake = Dokufy::fake();

    // Fail first two attempts, succeed on third
    $attempts = 0;
    $fake->shouldFailCallback(function () use (&$attempts) {
        $attempts++;
        if ($attempts < 3) {
            throw new ConversionException('Temporary failure');
        }
    });

    $invoice = Invoice::factory()->create();

    app(InvoiceGeneratorWithRetry::class)->generate($invoice);

    expect($attempts)->toBe(3);
    Dokufy::assertPdfGenerated();
});
```

## Pattern 5: Testing Multiple Documents

Test batch generation:

```php
it('generates PDFs for all unpaid invoices', function () {
    Dokufy::fake();

    $invoices = Invoice::factory()
        ->count(5)
        ->create(['status' => 'unpaid']);

    app(BulkInvoiceGenerator::class)->generateAll();

    Dokufy::assertGeneratedCount(5);

    foreach ($invoices as $invoice) {
        Dokufy::assertGenerated(storage_path("invoices/{$invoice->number}.pdf"));
    }
});
```

## Pattern 6: Testing Driver Selection

Test correct driver selection logic:

```php
describe('DocumentGenerator driver selection', function () {
    beforeEach(fn () => Dokufy::fake());

    it('uses Gotenberg for HTML in production', function () {
        config(['app.env' => 'production']);

        app(ReportGenerator::class)->generate();

        Dokufy::assertDriverUsed('gotenberg');
    });

    it('uses PHPWord for DOCX templates', function () {
        $generator = app(ContractGenerator::class);
        $generator->generate($contract);

        Dokufy::assertDriverUsed('phpword');
    });
});
```

## Pattern 7: Testing Data Transformations

Test that data is correctly transformed before generation:

```php
it('formats currency values correctly', function () {
    Dokufy::fake();

    $invoice = Invoice::factory()->create([
        'subtotal' => 1000.50,
        'tax' => 60.03,
        'total' => 1060.53,
    ]);

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertDataContains('subtotal', 'RM 1,000.50');
    Dokufy::assertDataContains('tax', 'RM 60.03');
    Dokufy::assertDataContains('total', 'RM 1,060.53');
});

it('formats dates according to locale', function () {
    Dokufy::fake();
    app()->setLocale('ms');

    $invoice = Invoice::factory()->create([
        'created_at' => '2025-01-15',
    ]);

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertDataContains('invoice_date', '15 Januari 2025');
});
```

## Pattern 8: Testing Output Paths

Test that files are saved to correct locations:

```php
it('saves to customer-specific directory', function () {
    Dokufy::fake();

    $customer = Customer::factory()->create(['id' => 123]);
    $invoice = Invoice::factory()->for($customer)->create();

    app(InvoiceGenerator::class)->generate($invoice);

    $fake = Dokufy::getFake();
    $path = $fake->getLastOutputPath();

    expect($path)->toContain('/customers/123/invoices/');
});

it('uses date-based directory structure', function () {
    Dokufy::fake();

    $invoice = Invoice::factory()->create([
        'created_at' => '2025-01-15',
    ]);

    app(InvoiceGenerator::class)->generate($invoice);

    $fake = Dokufy::getFake();
    $path = $fake->getLastOutputPath();

    expect($path)->toContain('/2025/01/');
});
```

## Pattern 9: Testing with Mocked Dependencies

Test document generation with mocked external services:

```php
it('includes exchange rate from API', function () {
    Dokufy::fake();

    // Mock exchange rate service
    $this->mock(ExchangeRateService::class, function ($mock) {
        $mock->shouldReceive('getRate')
            ->with('USD', 'MYR')
            ->andReturn(4.75);
    });

    $invoice = Invoice::factory()->create([
        'currency' => 'USD',
        'total' => 100,
    ]);

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertDataContains('exchange_rate', '4.75');
    Dokufy::assertDataContains('total_myr', 'RM 475.00');
});
```

## Pattern 10: Testing Events

Test that events are fired during generation:

```php
it('fires DocumentGenerated event after PDF creation', function () {
    Dokufy::fake();
    Event::fake([DocumentGenerated::class]);

    $invoice = Invoice::factory()->create();

    app(InvoiceGenerator::class)->generate($invoice);

    Event::assertDispatched(DocumentGenerated::class, function ($event) use ($invoice) {
        return $event->documentable->is($invoice)
            && $event->type === 'pdf';
    });
});
```

## Best Practices

### 1. Always Reset Between Tests

```php
afterEach(function () {
    Dokufy::clearFake();
});
```

### 2. Use Descriptive Test Names

```php
// Good
it('generates PDF with correct customer billing address');
it('uses executive template for C-level employees');

// Bad
it('works');
it('generates PDF');
```

### 3. Test One Thing Per Test

```php
// Good - separate tests
it('uses correct template', function () { /* ... */ });
it('includes customer data', function () { /* ... */ });
it('saves to correct path', function () { /* ... */ });

// Bad - testing too much
it('generates invoice correctly', function () {
    // Tests template, data, path, format, etc.
});
```

### 4. Use Factories Effectively

```php
// Create specific factory states
$invoice = Invoice::factory()
    ->unpaid()
    ->overdue()
    ->withItems(3)
    ->create();
```

## Next Steps

- [Examples](../06-examples/README.md) - Real-world implementations
- [API Reference](../04-api/README.md) - All available methods
