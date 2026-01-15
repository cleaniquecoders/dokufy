# Facade Methods

Complete reference for all methods available through the `Dokufy` facade.

## Namespace

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;
```

## Input Methods

### template()

Load a template file for processing.

```php
public function template(string $path): self
```

**Parameters:**

- `$path` - Absolute path to the template file (DOCX or HTML)

**Returns:** `self` for method chaining

**Throws:** `TemplateNotFoundException` if file doesn't exist

**Example:**

```php
Dokufy::template(resource_path('templates/invoice.docx'));
```

### html()

Set HTML content directly.

```php
public function html(string $content): self
```

**Parameters:**

- `$content` - HTML string to convert

**Returns:** `self` for method chaining

**Example:**

```php
Dokufy::html('<h1>Hello World</h1><p>Generated content.</p>');

// With Blade view
$html = view('documents.report', $data)->render();
Dokufy::html($html);
```

## Data Binding Methods

### data()

Set placeholder replacement data.

```php
public function data(array $data): self
```

**Parameters:**

- `$data` - Associative array of placeholder => value pairs

**Returns:** `self` for method chaining

**Example:**

```php
Dokufy::template($template)
    ->data([
        'customer_name' => 'John Doe',
        'invoice_date' => '2025-01-15',
        'total' => 'RM 1,500.00',
    ]);
```

### with()

Set a PlaceholderHandler for advanced data binding.

```php
public function with(object $handler): self
```

**Parameters:**

- `$handler` - PlaceholderHandler instance from Placeholdify package

**Returns:** `self` for method chaining

**Example:**

```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$handler = (new PlaceholderHandler)
    ->useContext('user', $user, 'u')
    ->addFormatted('amount', $amount, 'currency', 'MYR');

Dokufy::template($template)->with($handler);
```

## Output Methods

### toPdf()

Convert and save as PDF.

```php
public function toPdf(string $outputPath): string
```

**Parameters:**

- `$outputPath` - Absolute path for the output PDF file

**Returns:** `string` - The output path

**Throws:** `ConversionException` on failure

**Example:**

```php
$path = Dokufy::template($template)
    ->data($data)
    ->toPdf(storage_path('documents/output.pdf'));
```

### toDocx()

Save as DOCX with placeholders replaced.

```php
public function toDocx(string $outputPath): string
```

**Parameters:**

- `$outputPath` - Absolute path for the output DOCX file

**Returns:** `string` - The output path

**Throws:** `ConversionException` on failure

**Example:**

```php
$path = Dokufy::template($template)
    ->data($data)
    ->toDocx(storage_path('documents/output.docx'));
```

### stream()

Return a streamed response for inline viewing.

```php
public function stream(?string $filename = null): StreamedResponse
```

**Parameters:**

- `$filename` - Optional filename for Content-Disposition header

**Returns:** `Illuminate\Http\Response\StreamedResponse`

**Example:**

```php
// In a controller
public function preview(Invoice $invoice)
{
    return Dokufy::template($template)
        ->data($invoice->toArray())
        ->stream("invoice-{$invoice->number}.pdf");
}
```

### download()

Return a download response.

```php
public function download(?string $filename = null): BinaryFileResponse
```

**Parameters:**

- `$filename` - Optional filename for download

**Returns:** `Symfony\Component\HttpFoundation\BinaryFileResponse`

**Example:**

```php
// In a controller
public function download(Invoice $invoice)
{
    return Dokufy::template($template)
        ->data($invoice->toArray())
        ->download("invoice-{$invoice->number}.pdf");
}
```

## Driver Methods

### driver()

Select a specific driver for the next operation.

```php
public function driver(string $name): self
```

**Parameters:**

- `$name` - Driver name: `gotenberg`, `libreoffice`, `chromium`, `phpword`, `fake`

**Returns:** `self` for method chaining

**Throws:** `DriverException` if driver doesn't exist

**Example:**

```php
Dokufy::driver('libreoffice')
    ->template($template)
    ->data($data)
    ->toPdf($output);
```

### make()

Create a new Dokufy instance with optional driver.

```php
public function make(?string $driver = null): self
```

**Parameters:**

- `$driver` - Optional driver name, uses default if null

**Returns:** `self` - New Dokufy instance

**Example:**

```php
$dokufy = Dokufy::make('gotenberg');
$dokufy->html($html)->toPdf($output);
```

### getAvailableDrivers()

Get list of all registered drivers.

```php
public function getAvailableDrivers(): array
```

**Returns:** `array` - Array of driver names

**Example:**

```php
$drivers = Dokufy::getAvailableDrivers();
// ['gotenberg', 'libreoffice', 'chromium', 'phpword', 'fake']
```

### isDriverAvailable()

Check if a driver is available and configured.

```php
public function isDriverAvailable(string $driver): bool
```

**Parameters:**

- `$driver` - Driver name to check

**Returns:** `bool`

**Example:**

```php
if (Dokufy::isDriverAvailable('gotenberg')) {
    // Use Gotenberg
} else {
    // Fallback to another driver
}
```

## Testing Methods

### fake()

Enable fake driver for testing.

```php
public function fake(): FakeDriver
```

**Returns:** `FakeDriver` instance

**Example:**

```php
Dokufy::fake();

// Run code that generates documents

Dokufy::assertPdfGenerated();
```

### assertGenerated()

Assert a document was generated at the specified path.

```php
public function assertGenerated(string $path): void
```

**Parameters:**

- `$path` - Expected output path

**Example:**

```php
Dokufy::fake();

app(GenerateInvoice::class)->execute($invoice);

Dokufy::assertGenerated(storage_path("invoices/{$invoice->id}.pdf"));
```

### assertPdfGenerated()

Assert any PDF was generated.

```php
public function assertPdfGenerated(): void
```

**Example:**

```php
Dokufy::fake();

app(GenerateReport::class)->execute();

Dokufy::assertPdfGenerated();
```

### assertDocxGenerated()

Assert any DOCX was generated.

```php
public function assertDocxGenerated(): void
```

**Example:**

```php
Dokufy::fake();

app(GenerateContract::class)->asDocx();

Dokufy::assertDocxGenerated();
```

## Utility Methods

### reset()

Reset the Dokufy instance state.

```php
public function reset(): self
```

**Returns:** `self` for method chaining

**Example:**

```php
$dokufy = Dokufy::make();

$dokufy->template($t1)->data($d1)->toPdf($o1);
$dokufy->reset()->template($t2)->data($d2)->toPdf($o2);
```

## Method Chaining

All setter methods return `$this` for fluent chaining:

```php
Dokufy::driver('gotenberg')
    ->template(resource_path('templates/report.docx'))
    ->data([
        'title' => 'Monthly Report',
        'date' => now()->format('F Y'),
        'prepared_by' => auth()->user()->name,
    ])
    ->toPdf(storage_path('reports/monthly.pdf'));
```

## Next Steps

- [Contracts](02-contracts.md) - Interface definitions
- [Exceptions](03-exceptions.md) - Error handling
- [Examples](../06-examples/README.md) - Practical examples
