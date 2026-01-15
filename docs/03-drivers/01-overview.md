# Driver Overview

This guide helps you understand and choose the right driver for your use case.

## Available Drivers

Dokufy ships with five drivers:

| Driver | Class | Container Binding |
|--------|-------|-------------------|
| Gotenberg | `GotenbergDriver` | `dokufy.driver.gotenberg` |
| LibreOffice | `LibreOfficeDriver` | `dokufy.driver.libreoffice` |
| Chromium | `ChromiumDriver` | `dokufy.driver.chromium` |
| PHPWord | `PhpWordDriver` | `dokufy.driver.phpword` |
| Fake | `FakeDriver` | `dokufy.driver.fake` |

## Capability Matrix

### Supported Input Formats

| Format | Gotenberg | LibreOffice | Chromium | PHPWord |
|--------|-----------|-------------|----------|---------|
| HTML | Yes | Yes | Yes | No |
| DOCX | Yes | Yes | No | Yes |
| XLSX | Yes | Yes | No | No |
| PPTX | Yes | Yes | No | No |
| ODT | Yes | Yes | No | No |
| Markdown | Yes | No | No | No |

### Features

| Feature | Gotenberg | LibreOffice | Chromium | PHPWord |
|---------|-----------|-------------|----------|---------|
| CSS Support | Full | Limited | Full | None |
| JavaScript | Yes | No | Yes | No |
| Custom Fonts | Yes | Yes | Yes | Limited |
| Headers/Footers | Yes | Yes | Yes | Yes |
| Landscape | Yes | Yes | Yes | Yes |
| Page Numbers | Yes | Yes | Yes | Yes |

## Decision Tree

Use this flowchart to choose your driver:

```text
                    ┌─────────────────────┐
                    │  What's your input? │
                    └──────────┬──────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
         ┌────────┐       ┌────────┐       ┌────────┐
         │  HTML  │       │  DOCX  │       │ Excel/ │
         │        │       │        │       │  PPT   │
         └───┬────┘       └───┬────┘       └───┬────┘
             │                │                │
             ▼                ▼                ▼
    ┌─────────────────┐   ┌──────────┐    ┌──────────┐
    │ Need pixel-     │   │ Docker   │    │ Docker   │
    │ perfect CSS?    │   │ available│    │ available│
    └────────┬────────┘   └────┬─────┘    └────┬─────┘
             │                 │               │
      ┌──────┴──────┐    ┌─────┴─────┐   ┌─────┴─────┐
      │             │    │           │   │           │
      ▼             ▼    ▼           ▼   ▼           ▼
  ┌────────┐   ┌────────┐ ┌────────┐ ┌────────┐ ┌────────────┐
  │Chromium│   │Gotenberg││Gotenberg││PHPWord ││Gotenberg or│
  │        │   │        ││        ││        ││LibreOffice │
  └────────┘   └────────┘└────────┘└────────┘└────────────┘
```

## Environment Recommendations

### Production (Docker/Kubernetes)

**Recommended: Gotenberg**

- Containerized, scales horizontally
- Consistent output across environments
- No binary dependencies on host

```yaml
# docker-compose.yml
services:
  gotenberg:
    image: gotenberg/gotenberg:8
    ports:
      - "3000:3000"
```

### Production (Traditional VPS)

**Recommended: LibreOffice**

- No Docker overhead
- Direct system binary
- Good format support

```bash
# Ubuntu/Debian
apt-get install libreoffice

# CentOS/RHEL
yum install libreoffice
```

### Shared Hosting

**Recommended: PHPWord**

- No external dependencies
- Pure PHP implementation
- Limited to DOCX input

```bash
composer require phpoffice/phpword dompdf/dompdf
```

### Development/Testing

**Recommended: Fake**

- No setup required
- Fast execution
- Full assertion support

```php
Dokufy::fake();
```

## Performance Characteristics

| Driver | Cold Start | Warm Request | Memory |
|--------|------------|--------------|--------|
| Gotenberg | ~500ms | ~100ms | Low (external) |
| LibreOffice | ~2s | ~500ms | High |
| Chromium | ~1s | ~200ms | High |
| PHPWord | ~100ms | ~100ms | Medium |

> **Note**: These are approximate values. Actual performance depends on document complexity and system resources.

## Fallback Strategy

You can implement driver fallbacks for resilience:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

function generatePdf(string $template, array $data, string $output): string
{
    $drivers = ['gotenberg', 'libreoffice', 'phpword'];

    foreach ($drivers as $driver) {
        if (!Dokufy::isDriverAvailable($driver)) {
            continue;
        }

        try {
            return Dokufy::driver($driver)
                ->template($template)
                ->data($data)
                ->toPdf($output);
        } catch (DriverException $e) {
            continue;
        }
    }

    throw new RuntimeException('No available driver could process the document');
}
```

## Next Steps

- [Gotenberg Driver](02-gotenberg.md) - Full Gotenberg setup
- [LibreOffice Driver](03-libreoffice.md) - LibreOffice configuration
- [Configuration](../05-configuration/README.md) - All driver options
