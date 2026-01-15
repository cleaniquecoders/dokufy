# Invoice Generation

Complete examples for generating invoices with Dokufy.

## Basic Invoice

### DOCX Template Approach

Create a DOCX template with placeholders:

```text
INVOICE #{{ invoice_number }}
Date: {{ invoice_date }}

Bill To:
{{ customer_name }}
{{ customer_address }}

| Item | Quantity | Price | Total |
|------|----------|-------|-------|
{{ items }}

Subtotal: {{ subtotal }}
Tax ({{ tax_rate }}): {{ tax_amount }}
Total: {{ total }}
```

Generate the PDF:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

$invoice = Invoice::with('items', 'customer')->find($id);

Dokufy::template(resource_path('templates/invoice.docx'))
    ->data([
        'invoice_number' => $invoice->number,
        'invoice_date' => $invoice->created_at->format('d F Y'),
        'customer_name' => $invoice->customer->name,
        'customer_address' => $invoice->customer->full_address,
        'subtotal' => number_format($invoice->subtotal, 2),
        'tax_rate' => $invoice->tax_rate . '%',
        'tax_amount' => number_format($invoice->tax_amount, 2),
        'total' => number_format($invoice->total, 2),
    ])
    ->toPdf(storage_path("invoices/{$invoice->number}.pdf"));
```

### HTML/Blade Approach

Create a Blade view `resources/views/invoices/pdf.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .invoice-details { float: right; text-align: right; }
        .customer-details { margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .totals { width: 300px; float: right; }
        .totals td { text-align: right; }
        .totals .total-row { font-weight: bold; font-size: 16px; background: #f5f5f5; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="invoice-details">
            <strong>Invoice #{{ $invoice->number }}</strong><br>
            Date: {{ $invoice->created_at->format('d F Y') }}<br>
            Due: {{ $invoice->due_date->format('d F Y') }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="customer-details">
        <strong>Bill To:</strong><br>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}<br>
        {{ $invoice->customer->city }}, {{ $invoice->customer->postcode }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>RM {{ number_format($item->unit_price, 2) }}</td>
                <td>RM {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td>RM {{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax ({{ $invoice->tax_rate }}%):</td>
            <td>RM {{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total:</td>
            <td>RM {{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="footer">
        Thank you for your business!<br>
        {{ $company->name }} | {{ $company->phone }} | {{ $company->email }}
    </div>
</body>
</html>
```

Generate the PDF:

```php
$html = view('invoices.pdf', [
    'invoice' => $invoice,
    'company' => Company::first(),
])->render();

Dokufy::html($html)
    ->toPdf(storage_path("invoices/{$invoice->number}.pdf"));
```

## Invoice Service Class

Create a dedicated service for invoice generation:

```php
<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Company;
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use Illuminate\Support\Facades\Storage;

class InvoiceGenerator
{
    public function generate(Invoice $invoice): string
    {
        $invoice->load('items', 'customer');
        $company = Company::first();

        $html = view('invoices.pdf', compact('invoice', 'company'))->render();

        $filename = "invoices/{$invoice->number}.pdf";
        $path = storage_path("app/{$filename}");

        Dokufy::html($html)->toPdf($path);

        return $path;
    }

    public function download(Invoice $invoice)
    {
        $invoice->load('items', 'customer');
        $company = Company::first();

        $html = view('invoices.pdf', compact('invoice', 'company'))->render();

        return Dokufy::html($html)
            ->download("invoice-{$invoice->number}.pdf");
    }

    public function stream(Invoice $invoice)
    {
        $invoice->load('items', 'customer');
        $company = Company::first();

        $html = view('invoices.pdf', compact('invoice', 'company'))->render();

        return Dokufy::html($html)
            ->stream("invoice-{$invoice->number}.pdf");
    }
}
```

## Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceGenerator;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceGenerator $generator
    ) {}

    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return $this->generator->download($invoice);
    }

    public function preview(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return $this->generator->stream($invoice);
    }

    public function generateAndEmail(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $path = $this->generator->generate($invoice);

        // Email the invoice
        Mail::to($invoice->customer->email)
            ->send(new InvoiceMail($invoice, $path));

        return back()->with('success', 'Invoice sent successfully');
    }
}
```

## Testing Invoice Generation

```php
<?php

use App\Models\Invoice;
use App\Services\InvoiceGenerator;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

beforeEach(function () {
    Dokufy::fake();
});

it('generates invoice PDF', function () {
    $invoice = Invoice::factory()
        ->has(InvoiceItem::factory()->count(3))
        ->create();

    app(InvoiceGenerator::class)->generate($invoice);

    Dokufy::assertPdfGenerated();
});

it('includes all invoice items', function () {
    $invoice = Invoice::factory()
        ->has(InvoiceItem::factory()->count(5))
        ->create();

    app(InvoiceGenerator::class)->generate($invoice);

    // Verify data was passed
    $fake = Dokufy::getFake();
    expect($fake->wasGenerated())->toBeTrue();
});
```

## Next Steps

- [Offer Letters](02-offer-letters.md) - HR document generation
- [Batch Processing](04-batch-processing.md) - Generating multiple invoices
