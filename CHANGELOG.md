# Changelog

All notable changes to `dokufy` will be documented in this file.

## First Release - 2026-01-15

### Overview

We are excited to announce the first stable release of Dokufy, a driver-based document generation and PDF conversion package for Laravel. Dokufy abstracts document generation behind a unified API, allowing you to swap between backends via configuration without changing your code.


---

### Features

#### Unified Document Generation API

A fluent, chainable API for generating documents:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::template(resource_path('templates/invoice.docx'))
    ->data(['name' => 'John Doe', 'amount' => 'RM 1,500.00'])
    ->toPdf(storage_path('documents/invoice.pdf'));

```
#### Multiple Output Formats

- **PDF Generation** - Convert templates to PDF
- **DOCX Generation** - Generate Word documents
- **Streaming** - Stream PDFs directly to browser
- **Download** - Force download response

#### Five Production-Ready Drivers

| Driver | Backend | Best For |
|--------|---------|----------|
| GotenbergDriver | Docker/Gotenberg API | Production, CI/CD, Kubernetes |
| LibreOfficeDriver | LibreOffice CLI | Local development, VPS |
| ChromiumDriver | Puppeteer/Browsershot | Pixel-perfect HTML, Tailwind CSS |
| PhpWordDriver | Native PHP | Shared hosting, simple documents |
| FakeDriver | In-memory | Testing |

#### Template Processing

- Support for DOCX and HTML templates
- Flexible placeholder syntax: `{{ placeholder }}`
- Table row cloning for dynamic content

#### Placeholdify Integration

Seamless integration with `cleaniquecoders/placeholdify` for advanced placeholder handling:

```php
$handler = (new PlaceholderHandler())
    ->useContext('employee', $employee, 'emp')
    ->addFormatted('salary', $employee->salary, 'currency', 'MYR');

Dokufy::template($templatePath)
    ->with($handler)
    ->toPdf($outputPath);

```
#### Artisan Commands

- `dokufy:install` - Interactive installation wizard with driver detection
- `dokufy:generate` - CLI document generation with JSON data support
- `dokufy:status` - Display driver availability status

#### Testing Support

First-class testing support with FakeDriver:

```php
Dokufy::fake();

// Your code that generates documents...

Dokufy::assertPdfGenerated();
Dokufy::assertGenerated(storage_path('documents/invoice.pdf'));

```

---

### Installation

```bash
composer require cleaniquecoders/dokufy

```
Publish configuration:

```bash
php artisan vendor:publish --tag="dokufy-config"

```
Or use the interactive installer:

```bash
php artisan dokufy:install

```

---

### Configuration

```php
// config/dokufy.php
return [
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
        'format' => 'A4',
        'orientation' => 'portrait',
        'margin_top' => '1in',
        'margin_bottom' => '1in',
        'margin_left' => '0.5in',
        'margin_right' => '0.5in',
    ],

    'templates' => [
        'path' => resource_path('templates'),
    ],
];

```

---

### Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x

#### Driver-Specific Requirements

| Driver | Requirements |
|--------|--------------|
| Gotenberg | Docker with Gotenberg container |
| LibreOffice | `libreoffice` binary |
| Chromium | Node.js, Puppeteer |
| PhpWord | `phpoffice/phpword`, PDF renderer |
| Fake | None |


---

### API Reference

#### Input Methods

```php
Dokufy::template(string $path): self      // Set template file
Dokufy::html(string $content): self       // Set HTML content
Dokufy::data(array $data): self           // Set placeholder data
Dokufy::with(object $handler): self       // Set Placeholdify handler

```
#### Output Methods

```php
Dokufy::toPdf(string $outputPath): string           // Generate PDF
Dokufy::toDocx(string $outputPath): string          // Generate DOCX
Dokufy::stream(?string $filename): StreamedResponse // Stream to browser
Dokufy::download(?string $filename): BinaryFileResponse // Download

```
#### Driver Management

```php
Dokufy::driver(string $name): self        // Select driver
Dokufy::make(?string $driver): self       // Create new instance
Dokufy::getAvailableDrivers(): array      // List available drivers
Dokufy::isDriverAvailable(string $driver): bool

```
#### Testing

```php
Dokufy::fake(): FakeDriver
Dokufy::assertGenerated(string $path): void
Dokufy::assertPdfGenerated(): void
Dokufy::assertDocxGenerated(): void

```

---

### Supported Formats by Driver

| Format | Gotenberg | LibreOffice | Chromium | PhpWord |
|--------|-----------|-------------|----------|---------|
| HTML | Yes | Yes | Yes | Yes |
| DOCX | Yes | Yes | No | Yes |
| XLSX | Yes | Yes | No | No |
| PPTX | Yes | Yes | No | No |
| ODT | Yes | Yes | No | No |
| Markdown | Yes | No | No | No |


---

### Links

- **Documentation:** https://github.com/cleaniquecoders/dokufy
- **Issues:** https://github.com/cleaniquecoders/dokufy/issues
- **Author:** Nasrul Hazim Bin Mohamad


---

### License

MIT License


---

### Acknowledgements

Special thanks to the maintainers of:

- [Gotenberg](https://gotenberg.dev/)
- [PHPWord](https://github.com/PHPOffice/PHPWord)
- [Browsershot](https://github.com/spatie/browsershot)
- [Placeholdify](https://github.com/cleaniquecoders/placeholdify)
