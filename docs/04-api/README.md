# API Reference

Complete API documentation for the Dokufy package.

## Overview

This section provides detailed documentation for all public methods, classes, and contracts in Dokufy.

## Table of Contents

### [1. Facade Methods](01-facade.md)

All methods available through the `Dokufy` facade.

### [2. Contracts](02-contracts.md)

Interface definitions for `Converter`, `TemplateProcessor`, and `Driver`.

### [3. Exceptions](03-exceptions.md)

Exception classes and error handling.

## Quick Reference

### Most Used Methods

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Input
Dokufy::template(string $path): self
Dokufy::html(string $content): self

// Data binding
Dokufy::data(array $data): self
Dokufy::with(PlaceholderHandler $handler): self

// Output
Dokufy::toPdf(string $outputPath): string
Dokufy::toDocx(string $outputPath): string
Dokufy::stream(?string $filename = null): StreamedResponse
Dokufy::download(?string $filename = null): BinaryFileResponse

// Driver selection
Dokufy::driver(string $name): self
Dokufy::make(?string $driver = null): self
```

## Related Documentation

- [Getting Started](../01-getting-started/README.md) - Basic usage
- [Drivers](../03-drivers/README.md) - Driver-specific methods
- [Examples](../06-examples/README.md) - Practical examples
