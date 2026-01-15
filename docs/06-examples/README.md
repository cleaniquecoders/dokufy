# Examples

Real-world examples and common use cases for Dokufy.

## Overview

This section provides practical, copy-paste ready examples for common document generation scenarios.

## Table of Contents

### [1. Invoices](01-invoices.md)

Generate invoices with line items, totals, and customer details.

### [2. Offer Letters](02-offer-letters.md)

Generate HR documents with employee data and company details.

### [3. Reports](03-reports.md)

Generate data-driven reports from Blade views.

### [4. Batch Processing](04-batch-processing.md)

Generate multiple documents efficiently using queues.

## Quick Examples

### Basic PDF Generation

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::template(resource_path('templates/document.docx'))
    ->data(['name' => 'John Doe'])
    ->toPdf(storage_path('documents/output.pdf'));
```

### HTML to PDF

```php
$html = view('documents.report', $data)->render();

Dokufy::html($html)
    ->toPdf(storage_path('reports/monthly.pdf'));
```

### Download Response

```php
return Dokufy::template($template)
    ->data($data)
    ->download('document.pdf');
```

## Related Documentation

- [Getting Started](../01-getting-started/README.md) - Basic concepts
- [API Reference](../04-api/README.md) - All available methods
- [Testing](../07-testing/README.md) - Testing your implementations
