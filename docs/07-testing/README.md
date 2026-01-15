# Testing

Comprehensive guide to testing document generation with Dokufy.

## Overview

Dokufy provides a FakeDriver and assertion methods to make testing document
generation fast and reliable without requiring actual file I/O or external
services.

## Table of Contents

### [1. Getting Started](01-getting-started.md)

Setting up Dokufy testing in your test suite.

### [2. Assertions](02-assertions.md)

Complete reference for all assertion methods.

### [3. Testing Patterns](03-patterns.md)

Common testing patterns and best practices.

## Quick Start

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

it('generates a PDF', function () {
    Dokufy::fake();

    // Your code that generates documents
    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertPdfGenerated();
});
```

## Why Use the FakeDriver?

- **Speed**: No actual file I/O or external API calls
- **Isolation**: Tests don't depend on Gotenberg, LibreOffice, etc.
- **CI/CD Friendly**: No Docker or system dependencies needed
- **Assertions**: Rich assertion methods for verification

## Related Documentation

- [Fake Driver](../03-drivers/06-fake.md) - Detailed FakeDriver documentation
- [API Reference](../04-api/README.md) - All available methods
- [Examples](../06-examples/README.md) - Real-world test examples
