# Dokufy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/dokufy.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/dokufy)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/dokufy/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/dokufy/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/dokufy/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/dokufy/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/dokufy.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/dokufy)

**Generate PDFs your way — Gotenberg, LibreOffice, or native PHP.**

A driver-based document generation and PDF conversion package for Laravel.
Write code once, then swap between drivers via configuration — no code changes
required.

## Installation

Install the package via Composer:

```bash
composer require cleaniquecoders/dokufy
```

Run the installation command to set up the package:

```bash
php artisan dokufy:install
```

This will:

- Publish the configuration file
- Create the templates directory at `resources/templates`
- Create a sample HTML template
- Check driver availability and offer to install missing dependencies

Alternatively, you can manually publish the config file:

```bash
php artisan vendor:publish --tag="dokufy-config"
```

## Configuration

The published config file (`config/dokufy.php`) contains all driver settings:

```php
return [
    // Default driver: gotenberg, libreoffice, chromium, phpword, fake
    'default' => env('DOKUFY_DRIVER', 'phpword'),

    'drivers' => [
        'gotenberg' => [
            'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
            'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
        ],

        'libreoffice' => [
            'binary' => env('DOKUFY_LIBREOFFICE_BINARY', 'libreoffice'),
            'timeout' => env('DOKUFY_LIBREOFFICE_TIMEOUT', 120),
        ],

        'chromium' => [
            'node_binary' => env('DOKUFY_NODE_BINARY'),
            'npm_binary' => env('DOKUFY_NPM_BINARY'),
            'timeout' => env('DOKUFY_CHROMIUM_TIMEOUT', 60),
        ],

        'phpword' => [
            'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'),
        ],
    ],

    'pdf' => [
        'format' => env('DOKUFY_PDF_FORMAT', 'A4'),
        'orientation' => env('DOKUFY_PDF_ORIENTATION', 'portrait'),
        'margin_top' => env('DOKUFY_PDF_MARGIN_TOP', '1in'),
        'margin_bottom' => env('DOKUFY_PDF_MARGIN_BOTTOM', '1in'),
        'margin_left' => env('DOKUFY_PDF_MARGIN_LEFT', '0.5in'),
        'margin_right' => env('DOKUFY_PDF_MARGIN_RIGHT', '0.5in'),
    ],

    'templates' => [
        'path' => env('DOKUFY_TEMPLATES_PATH', resource_path('templates')),
    ],
];
```

## Usage

### Basic HTML to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Simple HTML string
Dokufy::html('<h1>Hello World</h1>')
    ->toPdf(storage_path('documents/hello.pdf'));

// With placeholder data
Dokufy::html('<h1>Hello {{ name }}</h1>')
    ->data(['name' => 'Ahmad'])
    ->toPdf(storage_path('documents/greeting.pdf'));
```

### Template to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// DOCX template with placeholders
Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->data([
        'name' => 'Ahmad bin Ali',
        'position' => 'Senior Developer',
        'salary' => 'RM 8,500.00',
    ])
    ->toPdf(storage_path('documents/offer.pdf'));

// HTML template file
Dokufy::template(resource_path('templates/invoice.html'))
    ->data(['invoice_number' => 'INV-001', 'total' => '1,500.00'])
    ->toPdf(storage_path('invoices/inv-001.pdf'));
```

### Template to DOCX

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Process placeholders and save as DOCX
Dokufy::template(resource_path('templates/contract.docx'))
    ->data(['client_name' => 'Acme Corp', 'date' => '15 January 2026'])
    ->toDocx(storage_path('contracts/acme-contract.docx'));
```

### Streaming and Downloads

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// In a controller - stream PDF inline (for preview)
public function preview(Invoice $invoice)
{
    return Dokufy::template(resource_path('templates/invoice.docx'))
        ->data($invoice->toArray())
        ->stream("invoice-{$invoice->number}.pdf");
}

// Download PDF as attachment
public function download(Invoice $invoice)
{
    return Dokufy::template(resource_path('templates/invoice.docx'))
        ->data($invoice->toArray())
        ->download("invoice-{$invoice->number}.pdf");
}
```

### Blade Views to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

$html = view('documents.invoice', compact('invoice'))->render();

Dokufy::html($html)
    ->toPdf(storage_path("invoices/{$invoice->id}.pdf"));
```

### Using a Specific Driver

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Force a specific driver
Dokufy::driver('gotenberg')
    ->html('<h1>Generated with Gotenberg</h1>')
    ->toPdf(storage_path('documents/gotenberg.pdf'));

// Create new instance with driver
Dokufy::make('chromium')
    ->html($htmlContent)
    ->toPdf($outputPath);
```

### With Placeholdify Integration

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$handler = (new PlaceholderHandler())
    ->useContext('employee', $employee, 'emp')
    ->addFormatted('salary', $employee->salary, 'currency', 'MYR')
    ->addDate('start_date', $employee->start_date, 'd F Y');

Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->with($handler)
    ->toPdf(storage_path('documents/offer.pdf'));
```

## Placeholder Syntax

Dokufy uses double curly braces for placeholders with flexible spacing:

```html
{{ name }}     <!-- standard -->
{{name}}       <!-- no spaces -->
{{ name}}      <!-- mixed -->
{{name }}      <!-- mixed -->
```

All variations are supported and will be replaced with the corresponding data value.

## Artisan Commands

### Check Driver Status

```bash
php artisan dokufy:status
```

Displays a table showing all drivers and their availability status.

### Generate Documents via CLI

```bash
# Basic usage
php artisan dokufy:generate input.html output.pdf

# With specific driver
php artisan dokufy:generate input.docx output.pdf --driver=gotenberg

# With placeholder data
php artisan dokufy:generate template.html output.pdf --data='{"name":"Ahmad"}'

# With data from JSON file
php artisan dokufy:generate template.docx output.pdf --data-file=data.json

# Overwrite existing output
php artisan dokufy:generate input.html output.pdf --force
```

## Available Drivers

### phpword (default)

- **Requirements:** `phpoffice/phpword`, `dompdf/dompdf`
- **Formats:** HTML, DOCX
- **Best for:** Shared hosting, simple documents

### gotenberg

- **Requirements:** Docker + Gotenberg container, `gotenberg/gotenberg-php`
- **Formats:** HTML, DOCX, XLSX, PPTX, ODT, Markdown
- **Best for:** Production, CI/CD, Kubernetes

### libreoffice

- **Requirements:** LibreOffice binary installed
- **Formats:** HTML, DOCX, XLSX, PPTX, ODT
- **Best for:** Local development, traditional VPS

### chromium

- **Requirements:** Node.js, `spatie/browsershot`, Puppeteer
- **Formats:** HTML only
- **Best for:** Pixel-perfect HTML rendering, Tailwind CSS

### fake

- **Requirements:** None
- **Formats:** All
- **Best for:** Testing

### Installing Driver Dependencies

```bash
# PHPWord driver (default)
composer require phpoffice/phpword dompdf/dompdf

# Gotenberg driver
composer require gotenberg/gotenberg-php
# Also requires: docker run -d -p 3000:3000 gotenberg/gotenberg:8

# Chromium driver
composer require spatie/browsershot
npm install puppeteer

# Placeholdify integration (optional)
composer require cleaniquecoders/placeholdify
```

## Testing

Dokufy provides a fake driver for testing:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

it('generates offer letter PDF', function () {
    Dokufy::fake();

    $employee = Employee::factory()->create();

    // Your code that generates documents
    app(GenerateOfferLetter::class)->execute($employee);

    // Assertions
    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated(storage_path("documents/offer-{$employee->id}.pdf"));
});

it('generates contract DOCX', function () {
    Dokufy::fake();

    // Your code
    app(GenerateContract::class)->execute($client);

    Dokufy::assertDocxGenerated();
});
```

Run tests:

```bash
composer test
```

## API Reference

### Facade Methods

```php
// Input methods
Dokufy::template(string $path): self      // Set template file (HTML or DOCX)
Dokufy::html(string $content): self       // Set HTML content directly
Dokufy::data(array $data): self           // Set placeholder data (can be chained)
Dokufy::with(object $handler): self       // Use Placeholdify handler

// Output methods
Dokufy::toPdf(string $outputPath): string              // Generate PDF file
Dokufy::toDocx(string $outputPath): string             // Generate DOCX file
Dokufy::stream(?string $filename = null): StreamedResponse    // Stream PDF inline
Dokufy::download(?string $filename = null): BinaryFileResponse // Download PDF

// Driver selection
Dokufy::driver(string $name): self        // Select specific driver
Dokufy::make(?string $driver = null): self // Create new instance

// Utilities
Dokufy::getAvailableDrivers(): array      // List available drivers
Dokufy::isDriverAvailable(string $driver): bool // Check driver availability
Dokufy::reset(): self                     // Reset instance state

// Testing
Dokufy::fake(): FakeDriver                // Use fake driver
Dokufy::assertGenerated(string $path): void    // Assert file generated
Dokufy::assertPdfGenerated(): void        // Assert PDF generated
Dokufy::assertDocxGenerated(): void       // Assert DOCX generated
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report
security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
