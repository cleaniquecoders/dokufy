# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Package Name:** `cleaniquecoders/dokufy`
**Namespace:** `CleaniqueCoders\Dokufy`
**Description:** A driver-based document generation and conversion package for Laravel. One API, any backend, zero rewrites.
**Tagline:** One API. Any backend. Zero rewrites.

## Purpose

Dokufy abstracts document generation and PDF conversion behind a unified API. Users write code once, then swap between Gotenberg (Docker), LibreOffice (CLI), Chromium (Node.js), or PHPWord (native PHP) via configuration — no code changes required.

## Related Packages

- **Placeholdify** (`cleaniquecoders/placeholdify`) - Placeholder replacement engine. Dokufy integrates with this for template data binding.
- **Gotenberg PHP** (`gotenberg/gotenberg-php`) - Official Gotenberg client. Used by GotenbergDriver.
- **PHPWord** (`phpoffice/phpword`) - DOCX manipulation. Used by PhpWordDriver.
- **Browsershot** (`spatie/browsershot`) - Chromium PDF generation. Used by ChromiumDriver.

## Architecture

### Core Concept: Driver Pattern

```
┌─────────────────────────────────────────────────────────┐
│                      Dokufy                              │
│                 (Unified API Layer)                      │
└─────────────────────────────────────────────────────────┘
        │              │              │              │
        ▼              ▼              ▼              ▼
   Gotenberg      LibreOffice      Chromium       PHPWord
   Driver         Driver           Driver         Driver
```

### Directory Structure

```
dokufy/
├── src/
│   ├── Dokufy.php                    # Main orchestrator class
│   ├── DokufyServiceProvider.php     # Laravel service provider
│   ├── Facades/
│   │   └── Dokufy.php                # Laravel facade
│   ├── Contracts/
│   │   ├── Converter.php             # PDF conversion contract
│   │   ├── TemplateProcessor.php     # Template manipulation contract
│   │   └── Driver.php                # Base driver contract
│   ├── Drivers/
│   │   ├── GotenbergDriver.php       # Gotenberg API driver
│   │   ├── LibreOfficeDriver.php     # LibreOffice CLI driver
│   │   ├── ChromiumDriver.php        # Browsershot/Chromium driver
│   │   ├── PhpWordDriver.php         # Native PHPWord driver
│   │   └── FakeDriver.php            # Testing driver
│   ├── Concerns/
│   │   └── InteractsWithPlaceholdify.php
│   └── Exceptions/
│       ├── ConversionException.php
│       ├── DriverException.php
│       ├── TemplateNotFoundException.php
│       └── DokufyException.php
├── config/
│   └── dokufy.php
├── tests/
│   ├── Unit/
│   │   ├── Drivers/
│   │   │   ├── GotenbergDriverTest.php
│   │   │   ├── LibreOfficeDriverTest.php
│   │   │   ├── ChromiumDriverTest.php
│   │   │   └── PhpWordDriverTest.php
│   │   └── DokufyTest.php
│   ├── Feature/
│   │   └── DocumentGenerationTest.php
│   └── TestCase.php
├── stubs/
│   └── dokufy/
│       └── driver.stub
├── composer.json
├── phpunit.xml.dist
├── phpstan.neon.dist
├── pint.json
├── CHANGELOG.md
├── LICENSE.md
├── README.md
└── CLAUDE.md
```

## Contracts

### Converter Contract

```php
<?php

namespace CleaniqueCoders\Dokufy\Contracts;

interface Converter
{
    /**
     * Convert HTML content to PDF.
     */
    public function htmlToPdf(string $html, string $outputPath): string;

    /**
     * Convert DOCX file to PDF.
     */
    public function docxToPdf(string $docxPath, string $outputPath): string;

    /**
     * Get supported input formats.
     *
     * @return array<string>
     */
    public function supports(): array;

    /**
     * Check if driver is available/configured.
     */
    public function isAvailable(): bool;
}
```

### TemplateProcessor Contract

```php
<?php

namespace CleaniqueCoders\Dokufy\Contracts;

interface TemplateProcessor
{
    /**
     * Load a template file.
     */
    public function load(string $templatePath): self;

    /**
     * Set placeholder values.
     *
     * @param array<string, mixed> $data
     */
    public function setValues(array $data): self;

    /**
     * Set values for table row cloning.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public function setTableRows(string $placeholder, array $rows): self;

    /**
     * Save processed template.
     */
    public function save(string $outputPath): string;
}
```

## Driver Implementations

### GotenbergDriver

- Uses `gotenberg/gotenberg-php` official client
- Requires Docker with Gotenberg container
- Supports: HTML, DOCX, XLSX, PPTX, ODT, Markdown
- Best for: Production, CI/CD, Kubernetes

### LibreOfficeDriver

- Uses LibreOffice CLI (`--headless` mode)
- Requires `libreoffice` binary installed
- Supports: HTML, DOCX, XLSX, PPTX, ODT
- Best for: Local development, traditional VPS

### ChromiumDriver

- Uses `spatie/browsershot`
- Requires Node.js and Puppeteer
- Supports: HTML only
- Best for: Pixel-perfect HTML rendering, Tailwind CSS

### PhpWordDriver

- Uses `phpoffice/phpword` with DomPDF/TCPDF/mPDF
- No external dependencies
- Supports: DOCX
- Best for: Shared hosting, simple documents

### FakeDriver

- In-memory driver for testing
- Records all method calls
- Returns predictable outputs
- Best for: Unit tests, CI without Docker

## Configuration

```php
<?php
// config/dokufy.php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "gotenberg", "libreoffice", "chromium", "phpword", "fake"
    |
    */
    'default' => env('DOKUFY_DRIVER', 'gotenberg'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    */
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
            'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'), // dompdf, tcpdf, mpdf
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Options
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'format' => env('DOKUFY_PDF_FORMAT', 'A4'),
        'orientation' => env('DOKUFY_PDF_ORIENTATION', 'portrait'),
        'margin_top' => env('DOKUFY_PDF_MARGIN_TOP', '1in'),
        'margin_bottom' => env('DOKUFY_PDF_MARGIN_BOTTOM', '1in'),
        'margin_left' => env('DOKUFY_PDF_MARGIN_LEFT', '0.5in'),
        'margin_right' => env('DOKUFY_PDF_MARGIN_RIGHT', '0.5in'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'path' => env('DOKUFY_TEMPLATES_PATH', resource_path('templates')),
    ],
];
```

## Public API (Facade Methods)

```php
// Basic usage
Dokufy::template(string $path): self
Dokufy::html(string $content): self
Dokufy::data(array $data): self
Dokufy::with(PlaceholderHandler $handler): self

// Output methods
Dokufy::toPdf(string $outputPath): string
Dokufy::toDocx(string $outputPath): string
Dokufy::stream(string $filename = null): StreamedResponse
Dokufy::download(string $filename = null): BinaryFileResponse

// Driver selection
Dokufy::driver(string $name): self
Dokufy::make(string $driver = null): self

// Utilities
Dokufy::getAvailableDrivers(): array
Dokufy::isDriverAvailable(string $driver): bool

// Testing
Dokufy::fake(): FakeDriver
Dokufy::assertGenerated(string $path): void
Dokufy::assertPdfGenerated(): void
Dokufy::assertDocxGenerated(): void
```

## Usage Examples

### Basic Usage

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Template with data array
Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->data([
        'name' => 'Ahmad bin Ali',
        'position' => 'Senior Developer',
        'salary' => 'RM 8,500.00',
    ])
    ->toPdf(storage_path('documents/offer.pdf'));
```

### With Placeholdify

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

### Explicit Driver Selection

```php
// Force specific driver
Dokufy::driver('libreoffice')
    ->template($templatePath)
    ->data($data)
    ->toPdf($outputPath);

// Create new instance with driver
Dokufy::make('gotenberg')
    ->html($htmlContent)
    ->toPdf($outputPath);
```

### HTML to PDF

```php
// From string
Dokufy::html('<h1>Hello World</h1>')
    ->toPdf(storage_path('documents/hello.pdf'));

// From Blade view
$html = view('documents.invoice', compact('invoice'))->render();

Dokufy::html($html)
    ->toPdf(storage_path("invoices/{$invoice->id}.pdf"));
```

### Streaming Response

```php
// In controller
public function download(Invoice $invoice)
{
    return Dokufy::template(resource_path('templates/invoice.docx'))
        ->data($invoice->toArray())
        ->download("invoice-{$invoice->number}.pdf");
}

// Stream without download
public function preview(Invoice $invoice)
{
    return Dokufy::template(resource_path('templates/invoice.docx'))
        ->data($invoice->toArray())
        ->stream("invoice-{$invoice->number}.pdf");
}
```

### Testing

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

it('generates offer letter PDF', function () {
    Dokufy::fake();

    $employee = Employee::factory()->create();

    // Call your code that generates documents
    app(GenerateOfferLetter::class)->execute($employee);

    Dokufy::assertPdfGenerated();
    Dokufy::assertGenerated(storage_path("documents/offer-{$employee->id}.pdf"));
});
```

## Dependencies

### Required

```json
{
    "require": {
        "php": "^8.2",
        "illuminate/contracts": "^10.0|^11.0|^12.0",
        "illuminate/http": "^10.0|^11.0|^12.0",
        "illuminate/support": "^10.0|^11.0|^12.0",
        "spatie/laravel-package-tools": "^1.16"
    }
}
```

### Suggested (Driver-specific)

```json
{
    "suggest": {
        "gotenberg/gotenberg-php": "Required for Gotenberg driver (^2.0)",
        "phpoffice/phpword": "Required for PHPWord driver and DOCX template processing (^1.1)",
        "spatie/browsershot": "Required for Chromium driver (^4.0)",
        "dompdf/dompdf": "PDF renderer option for PHPWord driver (^2.0)",
        "cleaniquecoders/placeholdify": "For advanced placeholder replacement (^1.0)"
    }
}
```

### Dev Dependencies

```json
{
    "require-dev": {
        "larastan/larastan": "^2.9|^3.0",
        "laravel/pint": "^1.14",
        "nunomaduro/collision": "^7.10|^8.1",
        "orchestra/testbench": "^8.0|^9.0|^10.0",
        "pestphp/pest": "^2.0|^3.0",
        "pestphp/pest-plugin-arch": "^2.0|^3.0",
        "pestphp/pest-plugin-laravel": "^2.0|^3.0",
        "phpstan/extension-installer": "^1.3",
        "phpstan/phpstan-deprecation-rules": "^1.1|^2.0",
        "phpstan/phpstan-phpunit": "^1.3|^2.0"
    }
}
```

## Coding Standards

### Style Guide

- Follow PSR-12
- Use Laravel Pint with Laravel preset
- Run `composer lint` before committing

### Static Analysis

- PHPStan level 8
- Run `composer analyse` before committing

### Testing

- Use Pest PHP
- Aim for 80%+ coverage on core classes
- Unit tests for each driver
- Feature tests for integration scenarios

## Commands Reference

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run a single test file
./vendor/bin/pest tests/ExampleTest.php

# Run a specific test by name
./vendor/bin/pest --filter="test name"

# Fix code style
composer lint

# Run static analysis
composer analyse

# All checks (before commit)
composer check
```

## Key Implementation Notes

### Driver Resolution

The package uses Laravel's service container for driver resolution. Each driver is registered as a singleton with the prefix `dokufy.driver.{name}`.

### Placeholdify Integration

When `with(PlaceholderHandler $handler)` is called, Dokufy expects the handler to have a `toArray()` method that returns resolved placeholder values. This may require adding the method to Placeholdify if not present.

### Template Processing Flow

1. Load template (DOCX or HTML)
2. Resolve data (from array or PlaceholderHandler)
3. Replace placeholders in content
4. Convert to output format (PDF or DOCX)
5. Save or stream response

### Error Handling

All driver exceptions should extend `DokufyException`. Specific exceptions:

- `DriverException` - Driver not available or misconfigured
- `ConversionException` - Conversion process failed
- `TemplateNotFoundException` - Template file not found

### Queue Compatibility

All drivers should be queue-safe. Avoid storing closures or non-serializable objects in the main class properties.

## Development Workflow

1. Create feature branch from `main`
2. Implement changes with tests
3. Run `composer check` (lint, analyse, test)
4. Create PR with descriptive title
5. Ensure CI passes
6. Merge after review

## Notes for Claude Code

- Use Spatie's package skeleton patterns
- Follow existing Cleanique Coders package conventions (see Placeholdify, Traitify)
- Prefer composition over inheritance
- All public methods should have docblocks
- Use PHP 8.2+ features (readonly, enums, named arguments where appropriate)
- Config values should have sensible defaults
- Every driver should implement `isAvailable()` check
