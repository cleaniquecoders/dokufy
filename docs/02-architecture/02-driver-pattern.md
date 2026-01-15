# Driver Pattern

Dokufy uses the Driver Pattern to abstract document generation behind a common interface while supporting multiple backends.

## How It Works

The Driver Pattern separates the "what" from the "how":

- **What**: Generate a PDF from this content
- **How**: Use Gotenberg, LibreOffice, Chromium, or PHPWord

Your application code uses the unified API, and the driver handles the implementation details.

## Contract Hierarchy

```text
                    ┌─────────────────┐
                    │    Converter    │
                    │    Interface    │
                    └────────┬────────┘
                             │
                             │ extends
                             ▼
                    ┌─────────────────┐
                    │     Driver      │
                    │    Interface    │
                    └────────┬────────┘
                             │
                             │ implements
        ┌────────────────────┼────────────────────┐
        │                    │                    │
        ▼                    ▼                    ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│   Gotenberg   │  │  LibreOffice  │  │   PHPWord     │
│    Driver     │  │    Driver     │  │    Driver     │
└───────────────┘  └───────────────┘  └───────────────┘
```

## Converter Interface

The `Converter` interface defines the conversion methods:

```php
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

## Driver Interface

The `Driver` interface extends `Converter` with additional methods:

```php
namespace CleaniqueCoders\Dokufy\Contracts;

interface Driver extends Converter
{
    /**
     * Get the driver name.
     */
    public function getName(): string;

    /**
     * Get the driver configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}
```

## Driver Registration

Drivers are registered in the service provider as singletons:

```php
// DokufyServiceProvider.php
public function register(): void
{
    $drivers = ['fake', 'gotenberg', 'libreoffice', 'chromium', 'phpword'];

    foreach ($drivers as $driver) {
        $this->app->singleton("dokufy.driver.{$driver}", function ($app) use ($driver) {
            $class = $this->resolveDriverClass($driver);
            return new $class($app['config']["dokufy.drivers.{$driver}"] ?? []);
        });
    }
}
```

This means:

- Drivers are instantiated lazily (only when first used)
- Each driver is a singleton (same instance reused)
- Configuration is injected from `config/dokufy.php`

## Driver Resolution

When you call `Dokufy::driver('name')`, the system:

1. Looks up the binding `dokufy.driver.{name}` in the container
2. Instantiates the driver if not already created
3. Returns the driver instance

```php
// In Dokufy class
public function driver(string $name): self
{
    $this->currentDriver = app("dokufy.driver.{$name}");
    return $this;
}
```

## Format Support by Driver

Each driver implements `supports()` to declare supported formats:

| Driver | HTML | DOCX | XLSX | PPTX | ODT | Markdown |
|--------|------|------|------|------|-----|----------|
| Gotenberg | Yes | Yes | Yes | Yes | Yes | Yes |
| LibreOffice | Yes | Yes | Yes | Yes | Yes | No |
| Chromium | Yes | No | No | No | No | No |
| PHPWord | No | Yes | No | No | No | No |
| Fake | Yes | Yes | Yes | Yes | Yes | Yes |

## Availability Checks

Each driver implements `isAvailable()` differently:

- **Gotenberg**: Checks package class exists + HTTP health endpoint
- **LibreOffice**: Checks binary exists via `which` command
- **Chromium**: Checks Browsershot class exists
- **PHPWord**: Checks PHPWord and PDF renderer classes exist
- **Fake**: Always returns `true`

## Creating Custom Drivers

You can create custom drivers by implementing the `Driver` interface:

```php
namespace App\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;

class WkhtmltopdfDriver implements Driver
{
    public function __construct(private array $config = []) {}

    public function getName(): string
    {
        return 'wkhtmltopdf';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function htmlToPdf(string $html, string $outputPath): string
    {
        // Implementation using wkhtmltopdf
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        throw new \RuntimeException('DOCX not supported');
    }

    public function supports(): array
    {
        return ['html'];
    }

    public function isAvailable(): bool
    {
        return !empty(shell_exec('which wkhtmltopdf'));
    }
}
```

Register it in a service provider:

```php
$this->app->singleton('dokufy.driver.wkhtmltopdf', function ($app) {
    return new \App\Drivers\WkhtmltopdfDriver(
        config('dokufy.drivers.wkhtmltopdf', [])
    );
});
```

## Next Steps

- [Processing Flow](03-processing-flow.md) - How requests are processed
- [Drivers](../03-drivers/README.md) - Detailed driver documentation
