# PHPWord Driver

The PHPWord driver uses [PHPWord](https://phpword.readthedocs.io/) for DOCX
processing and DomPDF/TCPDF/mPDF for PDF rendering. It's a pure PHP solution
that works on shared hosting without external dependencies.

## Requirements

- PHP 8.2+
- `phpoffice/phpword` package
- PDF renderer (one of): `dompdf/dompdf`, `tecnickcom/tcpdf`, or `mpdf/mpdf`

## Installation

Install PHPWord:

```bash
composer require phpoffice/phpword
```

Install a PDF renderer (choose one):

```bash
# DomPDF (recommended, easiest setup)
composer require dompdf/dompdf

# TCPDF (better for complex layouts)
composer require tecnickcom/tcpdf

# mPDF (best Unicode support)
composer require mpdf/mpdf
```

## Configuration

```php
// config/dokufy.php
'drivers' => [
    'phpword' => [
        'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'), // dompdf, tcpdf, mpdf
    ],
],
```

### Environment Variables

```bash
# .env
DOKUFY_DRIVER=phpword
DOKUFY_PDF_RENDERER=dompdf
```

## Supported Formats

| Input | Output |
|-------|--------|
| DOCX | PDF |
| DOCX | DOCX (with replacements) |

> **Note**: The PHPWord driver only supports DOCX input. For HTML conversion, use Chromium or Gotenberg.

## Usage Examples

### DOCX to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::driver('phpword')
    ->template(resource_path('templates/contract.docx'))
    ->data([
        'client_name' => 'Acme Corporation',
        'contract_date' => '15 January 2025',
        'contract_value' => 'RM 50,000.00',
    ])
    ->toPdf(storage_path('contracts/acme-contract.pdf'));
```

### DOCX to DOCX (Template Processing)

```php
Dokufy::driver('phpword')
    ->template(resource_path('templates/offer-letter.docx'))
    ->data([
        'employee_name' => 'John Smith',
        'position' => 'Senior Developer',
        'start_date' => '1 February 2025',
    ])
    ->toDocx(storage_path('letters/john-offer.docx'));
```

## Template Placeholders

PHPWord templates use `${placeholder}` syntax by default. Dokufy normalizes both styles:

| Dokufy Style | PHPWord Style |
|--------------|---------------|
| `{{ name }}` | `${name}` |
| `{{name}}` | `${name}` |

Create your DOCX template in Microsoft Word or LibreOffice:

```text
Dear ${client_name},

This contract is dated ${contract_date} for the amount of ${contract_value}.

Regards,
Your Company
```

## PDF Renderer Comparison

| Feature | DomPDF | TCPDF | mPDF |
|---------|--------|-------|------|
| Setup | Easiest | Medium | Medium |
| Speed | Fast | Fast | Medium |
| CSS Support | Basic | Basic | Good |
| Unicode | Limited | Good | Best |
| RTL Languages | No | Yes | Yes |
| Memory Usage | Low | Medium | High |

### Choosing a Renderer

- **DomPDF**: Best for simple documents, English content
- **TCPDF**: Best for forms, barcodes, complex layouts
- **mPDF**: Best for multilingual documents, Arabic/Chinese/Thai

## Limitations

- **DOCX only**: Cannot process HTML, XLSX, or PPTX
- **Basic CSS**: PDF renderers have limited CSS support
- **No JavaScript**: Cannot execute dynamic content
- **Font limitations**: Custom fonts require manual setup

## Working with Tables

PHPWord supports table row cloning for dynamic tables:

```php
// Template has a table with placeholders:
// | ${item_name} | ${item_qty} | ${item_price} |

Dokufy::driver('phpword')
    ->template(resource_path('templates/invoice.docx'))
    ->data([
        'invoice_number' => 'INV-001',
        'customer' => 'John Doe',
    ])
    ->setTableRows('item', [
        ['item_name' => 'Widget A', 'item_qty' => '10', 'item_price' => 'RM 100.00'],
        ['item_name' => 'Widget B', 'item_qty' => '5', 'item_price' => 'RM 75.00'],
        ['item_name' => 'Widget C', 'item_qty' => '20', 'item_price' => 'RM 200.00'],
    ])
    ->toPdf(storage_path('invoices/inv-001.pdf'));
```

## Custom Fonts

To use custom fonts, register them with the PDF renderer:

### DomPDF

```php
// Create a font directory
// storage/fonts/

// Register in config
'phpword' => [
    'pdf_renderer' => 'dompdf',
    'font_dir' => storage_path('fonts'),
],
```

### mPDF

```php
'phpword' => [
    'pdf_renderer' => 'mpdf',
    'temp_dir' => storage_path('mpdf-temp'),
],
```

## Troubleshooting

### Memory Exhausted

```text
Error: Allowed memory size exhausted
```

**Solution**: Increase PHP memory limit or use a streaming approach:

```php
// In php.ini
memory_limit = 256M

// Or at runtime
ini_set('memory_limit', '256M');
```

### Font Not Found

```text
Error: Font 'Arial' not found
```

**Solution**: Use a font that exists or install it:

```bash
# Ubuntu/Debian
sudo apt-get install ttf-mscorefonts-installer
```

Or use generic font families in your template:

- `serif` instead of `Times New Roman`
- `sans-serif` instead of `Arial`
- `monospace` instead of `Courier New`

### Corrupted DOCX Output

The output DOCX file cannot be opened.

**Solution**: Ensure the template is a valid DOCX file:

```bash
# Test the template
unzip -t template.docx
```

### Images Not Rendering

Images in the DOCX don't appear in PDF.

**Solution**: PHPWord has limited image support. Ensure images are embedded (not linked) in the DOCX template.

## Best Practices

1. **Keep templates simple**: Avoid complex formatting
2. **Use standard fonts**: Stick to common system fonts
3. **Test templates**: Verify templates work before production
4. **Handle errors**: Wrap conversions in try-catch

```php
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

try {
    Dokufy::driver('phpword')
        ->template($template)
        ->data($data)
        ->toPdf($output);
} catch (ConversionException $e) {
    Log::error('PDF conversion failed', ['error' => $e->getMessage()]);
    // Handle gracefully
}
```

## Checking Availability

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

if (Dokufy::isDriverAvailable('phpword')) {
    // PHPWord and PDF renderer are available
}
```

## Next Steps

- [Fake Driver](06-fake.md) - For testing
- [Configuration](../05-configuration/README.md) - All configuration options
- [Examples](../06-examples/README.md) - Real-world examples
