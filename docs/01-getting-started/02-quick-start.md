# Quick Start

Generate your first PDF in under 5 minutes.

## Prerequisites

Ensure you have [installed Dokufy](01-installation.md) and at least one driver.

## Generate PDF from HTML

The simplest way to create a PDF:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::html('<h1>Hello World</h1><p>This is my first PDF.</p>')
    ->toPdf(storage_path('documents/hello.pdf'));
```

## Generate PDF from a Template

Create a DOCX template with placeholders and replace them with data:

### Step 1: Create a Template

Create a file at `resources/templates/welcome.docx` with content:

```text
Dear {{ name }},

Welcome to {{ company }}!

Best regards,
The Team
```

### Step 2: Generate the PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::template(resource_path('templates/welcome.docx'))
    ->data([
        'name' => 'Ahmad',
        'company' => 'Acme Corp',
    ])
    ->toPdf(storage_path('documents/welcome.pdf'));
```

## Generate PDF from a Blade View

Render a Blade view to HTML, then convert to PDF:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

$invoice = Invoice::find(1);
$html = view('invoices.show', compact('invoice'))->render();

Dokufy::html($html)
    ->toPdf(storage_path("invoices/invoice-{$invoice->id}.pdf"));
```

## Download PDF in Controller

Return a PDF download response from a controller:

```php
namespace App\Http\Controllers;

use CleaniqueCoders\Dokufy\Facades\Dokufy;

class InvoiceController extends Controller
{
    public function download(Invoice $invoice)
    {
        return Dokufy::template(resource_path('templates/invoice.docx'))
            ->data($invoice->toArray())
            ->download("invoice-{$invoice->number}.pdf");
    }
}
```

## Choose a Different Driver

By default, Dokufy uses the driver specified in your config. To use a specific driver:

```php
Dokufy::driver('libreoffice')
    ->template($templatePath)
    ->data($data)
    ->toPdf($outputPath);
```

## Next Steps

- [Basic Usage](03-basic-usage.md) - Learn all the core concepts
- [Drivers](../03-drivers/README.md) - Understand each driver's capabilities
- [Examples](../06-examples/README.md) - See real-world use cases
