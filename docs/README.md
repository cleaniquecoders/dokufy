# Documentation

Welcome to the Dokufy documentation. Dokufy is a driver-based document generation
and PDF conversion package for Laravel that provides a unified API for multiple
backends.

**One API. Any backend. Zero rewrites.**

## Overview

Dokufy abstracts document generation and PDF conversion behind a unified API.
Write your code once, then swap between Gotenberg (Docker), LibreOffice (CLI),
Chromium (Node.js), or PHPWord (native PHP) via configuration - no code changes
required.

## Documentation Structure

### [01. Getting Started](01-getting-started/README.md)

Installation, requirements, and your first document generation.

### [02. Architecture](02-architecture/README.md)

System design, driver pattern, and how Dokufy works under the hood.

### [03. Drivers](03-drivers/README.md)

Detailed documentation for each driver: Gotenberg, LibreOffice, Chromium, PHPWord, and Fake.

### [04. API Reference](04-api/README.md)

Complete API reference for the Dokufy facade and core classes.

### [05. Configuration](05-configuration/README.md)

Configuration options, environment variables, and PDF settings.

### [06. Examples](06-examples/README.md)

Real-world examples and common use cases.

### [07. Testing](07-testing/README.md)

Testing strategies using the FakeDriver and assertion methods.

## Quick Start

New to Dokufy? Start with [Getting Started](01-getting-started/README.md).

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Generate PDF from a DOCX template
Dokufy::template(resource_path('templates/invoice.docx'))
    ->data(['customer' => 'John Doe', 'total' => 'RM 1,500.00'])
    ->toPdf(storage_path('invoices/invoice-001.pdf'));

// Generate PDF from HTML
Dokufy::html('<h1>Hello World</h1>')
    ->toPdf(storage_path('documents/hello.pdf'));
```

## Finding Information

- **Installation & Setup**: Check [Getting Started](01-getting-started/README.md)
- **How drivers work**: Check [Architecture](02-architecture/README.md)
- **Driver-specific setup**: Check [Drivers](03-drivers/README.md)
- **API methods**: Check [API Reference](04-api/README.md)
- **Configuration options**: Check [Configuration](05-configuration/README.md)
- **Code examples**: Check [Examples](06-examples/README.md)
- **Testing your code**: Check [Testing](07-testing/README.md)
