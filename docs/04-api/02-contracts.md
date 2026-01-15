# Contracts

Dokufy defines contracts (interfaces) that all drivers must implement. This
ensures consistent behavior across different backends.

## Converter Contract

The primary contract for document conversion.

### Namespace

```php
namespace CleaniqueCoders\Dokufy\Contracts;
```

### Interface Definition

```php
interface Converter
{
    /**
     * Convert HTML content to PDF.
     *
     * @param string $html HTML content to convert
     * @param string $outputPath Path to save the PDF
     * @return string The output path
     * @throws ConversionException If conversion fails
     */
    public function htmlToPdf(string $html, string $outputPath): string;

    /**
     * Convert DOCX file to PDF.
     *
     * @param string $docxPath Path to the DOCX file
     * @param string $outputPath Path to save the PDF
     * @return string The output path
     * @throws ConversionException If conversion fails
     */
    public function docxToPdf(string $docxPath, string $outputPath): string;

    /**
     * Get supported input formats.
     *
     * @return array<string> List of supported format extensions
     */
    public function supports(): array;

    /**
     * Check if driver is available/configured.
     *
     * @return bool True if driver can be used
     */
    public function isAvailable(): bool;
}
```

### Implementation Example

```php
use CleaniqueCoders\Dokufy\Contracts\Converter;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

class CustomDriver implements Converter
{
    public function htmlToPdf(string $html, string $outputPath): string
    {
        // Your conversion logic
        file_put_contents($outputPath, $this->convert($html));

        return $outputPath;
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        // Your conversion logic
        $content = file_get_contents($docxPath);
        file_put_contents($outputPath, $this->convert($content));

        return $outputPath;
    }

    public function supports(): array
    {
        return ['html', 'docx'];
    }

    public function isAvailable(): bool
    {
        return true; // Check if your driver dependencies are available
    }
}
```

## TemplateProcessor Contract

Contract for template manipulation and placeholder replacement.

### Interface Definition

```php
interface TemplateProcessor
{
    /**
     * Load a template file.
     *
     * @param string $templatePath Path to the template
     * @return self
     * @throws TemplateNotFoundException If template doesn't exist
     */
    public function load(string $templatePath): self;

    /**
     * Set placeholder values.
     *
     * @param array<string, mixed> $data Placeholder => value pairs
     * @return self
     */
    public function setValues(array $data): self;

    /**
     * Set values for table row cloning.
     *
     * @param string $placeholder Table row placeholder name
     * @param array<int, array<string, mixed>> $rows Array of row data
     * @return self
     */
    public function setTableRows(string $placeholder, array $rows): self;

    /**
     * Save processed template.
     *
     * @param string $outputPath Path to save the processed template
     * @return string The output path
     */
    public function save(string $outputPath): string;
}
```

### Usage Example

```php
$processor = app(TemplateProcessor::class);

$path = $processor
    ->load(resource_path('templates/invoice.docx'))
    ->setValues([
        'invoice_number' => 'INV-001',
        'customer' => 'John Doe',
        'date' => '2025-01-15',
    ])
    ->setTableRows('items', [
        ['name' => 'Product A', 'qty' => '2', 'price' => 'RM 100'],
        ['name' => 'Product B', 'qty' => '1', 'price' => 'RM 50'],
    ])
    ->save(storage_path('invoices/inv-001.docx'));
```

## Driver Contract

Base contract that all drivers extend.

### Interface Definition

```php
interface Driver extends Converter
{
    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get driver configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Set driver configuration.
     *
     * @param array<string, mixed> $config
     * @return self
     */
    public function setConfig(array $config): self;
}
```

## Creating Custom Drivers

To create a custom driver, implement the `Driver` contract:

```php
<?php

namespace App\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

class CloudPdfDriver implements Driver
{
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'cloudpdf';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function htmlToPdf(string $html, string $outputPath): string
    {
        $response = Http::post($this->config['api_url'], [
            'html' => $html,
            'api_key' => $this->config['api_key'],
        ]);

        if (!$response->successful()) {
            throw ConversionException::withMessage('Cloud PDF API failed');
        }

        file_put_contents($outputPath, $response->body());

        return $outputPath;
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        $response = Http::attach('file', file_get_contents($docxPath), 'document.docx')
            ->post($this->config['api_url'] . '/docx', [
                'api_key' => $this->config['api_key'],
            ]);

        if (!$response->successful()) {
            throw ConversionException::withMessage('Cloud PDF API failed');
        }

        file_put_contents($outputPath, $response->body());

        return $outputPath;
    }

    public function supports(): array
    {
        return ['html', 'docx', 'xlsx'];
    }

    public function isAvailable(): bool
    {
        return !empty($this->config['api_key'])
            && !empty($this->config['api_url']);
    }
}
```

### Registering Custom Drivers

Register your driver in a service provider:

```php
<?php

namespace App\Providers;

use App\Dokufy\Drivers\CloudPdfDriver;
use Illuminate\Support\ServiceProvider;

class DokufyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('dokufy.driver.cloudpdf', function ($app) {
            return new CloudPdfDriver([
                'api_url' => config('services.cloudpdf.url'),
                'api_key' => config('services.cloudpdf.key'),
            ]);
        });
    }
}
```

Then use it:

```php
Dokufy::driver('cloudpdf')
    ->html($html)
    ->toPdf($outputPath);
```

## Next Steps

- [Exceptions](03-exceptions.md) - Error handling
- [Drivers](../03-drivers/README.md) - Built-in driver implementations
