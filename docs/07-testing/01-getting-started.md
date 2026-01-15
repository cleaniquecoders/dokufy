# Getting Started with Testing

Set up Dokufy testing in your Laravel application.

## Setup

### PHPUnit Configuration

No special configuration needed. The FakeDriver works out of the box.

### Pest PHP Setup

For Pest, you may want to add a helper in `tests/Pest.php`:

```php
<?php

use CleaniqueCoders\Dokufy\Facades\Dokufy;

uses(Tests\TestCase::class)->in('Feature', 'Unit');

// Reset Dokufy fake after each test
afterEach(function () {
    Dokufy::clearFake();
});
```

## Basic Test Structure

### Using Pest

```php
<?php

use CleaniqueCoders\Dokufy\Facades\Dokufy;
use App\Services\InvoiceGenerator;

beforeEach(function () {
    Dokufy::fake();
});

it('generates invoice PDF', function () {
    $invoice = Invoice::factory()->create();

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertPdfGenerated();
});
```

### Using PHPUnit

```php
<?php

namespace Tests\Feature;

use CleaniqueCoders\Dokufy\Facades\Dokufy;
use App\Services\InvoiceGenerator;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Dokufy::fake();
    }

    public function test_generates_invoice_pdf(): void
    {
        $invoice = Invoice::factory()->create();

        app(InvoiceGenerator::class)->generate($invoice);

        Dokufy::assertPdfGenerated();
    }
}
```

## Test Environment Configuration

### Environment File

Create `.env.testing`:

```bash
DOKUFY_DRIVER=fake
```

### Config Override

Or set in `phpunit.xml`:

```xml
<env name="DOKUFY_DRIVER" value="fake"/>
```

## Testing Different Scenarios

### Testing PDF Generation

```php
it('generates PDF from template', function () {
    Dokufy::fake();

    Dokufy::template(resource_path('templates/invoice.docx'))
        ->data(['name' => 'John'])
        ->toPdf(storage_path('output.pdf'));

    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated(storage_path('output.pdf'));
});
```

### Testing HTML to PDF

```php
it('generates PDF from HTML', function () {
    Dokufy::fake();

    Dokufy::html('<h1>Hello World</h1>')
        ->toPdf(storage_path('hello.pdf'));

    Dokufy::assertPdfGenerated();
});
```

### Testing DOCX Output

```php
it('generates DOCX output', function () {
    Dokufy::fake();

    Dokufy::template(resource_path('templates/contract.docx'))
        ->data(['party' => 'Company A'])
        ->toDocx(storage_path('contract.docx'));

    Dokufy::assertDocxGenerated();
});
```

### Testing Download Response

```php
it('returns downloadable response', function () {
    Dokufy::fake();

    $response = Dokufy::template(resource_path('templates/invoice.docx'))
        ->data(['invoice_number' => 'INV-001'])
        ->download('invoice.pdf');

    expect($response)->toBeInstanceOf(BinaryFileResponse::class);
});
```

## Testing Services

When testing your own services that use Dokufy:

```php
<?php

namespace App\Services;

use CleaniqueCoders\Dokufy\Facades\Dokufy;

class ReportGenerator
{
    public function generateMonthlyReport(int $year, int $month): string
    {
        $data = $this->collectReportData($year, $month);
        $html = view('reports.monthly', $data)->render();

        $filename = "report-{$year}-{$month}.pdf";
        $path = storage_path("app/reports/{$filename}");

        Dokufy::html($html)->toPdf($path);

        return $path;
    }
}
```

Test:

```php
<?php

use App\Services\ReportGenerator;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

beforeEach(function () {
    Dokufy::fake();
});

it('generates monthly report', function () {
    $path = app(ReportGenerator::class)->generateMonthlyReport(2025, 1);

    expect($path)->toContain('report-2025-1.pdf');
    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated($path);
});
```

## Testing with Database

```php
it('generates invoice with database data', function () {
    Dokufy::fake();

    $customer = Customer::factory()->create(['name' => 'Acme Corp']);
    $invoice = Invoice::factory()
        ->for($customer)
        ->has(InvoiceItem::factory()->count(3))
        ->create();

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertPdfGenerated();
    Dokufy::assertDataContains('customer_name', 'Acme Corp');
});
```

## Next Steps

- [Assertions](02-assertions.md) - All available assertions
- [Testing Patterns](03-patterns.md) - Advanced patterns
