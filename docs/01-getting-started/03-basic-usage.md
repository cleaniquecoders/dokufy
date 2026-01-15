# Basic Usage

This guide covers the core concepts and methods for using Dokufy.

## Input Methods

Dokufy accepts content from templates or raw HTML.

### Templates

Load a DOCX or HTML template file:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::template(resource_path('templates/contract.docx'));
```

Supported template formats depend on your driver:

| Driver | Supported Formats |
|--------|-------------------|
| Gotenberg | HTML, DOCX, XLSX, PPTX, ODT, Markdown |
| LibreOffice | HTML, DOCX, XLSX, PPTX, ODT |
| Chromium | HTML only |
| PHPWord | DOCX only |

### HTML Content

Pass HTML content directly:

```php
Dokufy::html('<h1>Report</h1><p>Generated content here.</p>');
```

You can use Blade views:

```php
$html = view('reports.monthly', ['data' => $reportData])->render();
Dokufy::html($html);
```

## Data Binding

Replace placeholders in templates with actual values.

### Using an Array

```php
Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->data([
        'name' => 'Ahmad bin Ali',
        'position' => 'Senior Developer',
        'salary' => 'RM 8,500.00',
        'start_date' => '1 February 2025',
    ]);
```

### Placeholder Syntax

Dokufy supports flexible placeholder syntax:

- `{{ key }}` - spaces around key
- `{{key}}` - no spaces
- `{{ key}}` - space before
- `{{key }}` - space after

All variations work and will be replaced with the corresponding value from your data array.

### Using Placeholdify

For advanced placeholder handling, use the [Placeholdify](https://github.com/cleaniquecoders/placeholdify) package:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$handler = (new PlaceholderHandler)
    ->useContext('employee', $employee, 'emp')
    ->addFormatted('salary', $employee->salary, 'currency', 'MYR')
    ->addDate('start_date', $employee->start_date, 'd F Y');

Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->with($handler)
    ->toPdf(storage_path('documents/offer.pdf'));
```

## Output Methods

Dokufy provides several ways to output the generated document.

### Save to File

Save as PDF:

```php
$path = Dokufy::template($template)
    ->data($data)
    ->toPdf(storage_path('documents/output.pdf'));
```

Save as DOCX (template copy with placeholders replaced):

```php
$path = Dokufy::template($template)
    ->data($data)
    ->toDocx(storage_path('documents/output.docx'));
```

### Download Response

Return a download response from a controller:

```php
return Dokufy::template($template)
    ->data($data)
    ->download('document.pdf');
```

### Stream Response

Stream the PDF inline (for preview in browser):

```php
return Dokufy::template($template)
    ->data($data)
    ->stream('document.pdf');
```

## Driver Selection

### Default Driver

The default driver is configured in `config/dokufy.php`:

```php
'default' => env('DOKUFY_DRIVER', 'gotenberg'),
```

### Runtime Selection

Select a driver at runtime:

```php
Dokufy::driver('chromium')
    ->html($html)
    ->toPdf($outputPath);
```

### Create New Instance

Create a fresh Dokufy instance with a specific driver:

```php
$dokufy = Dokufy::make('libreoffice');
$dokufy->template($template)->data($data)->toPdf($output);
```

## Method Chaining

All setter methods return `$this` for fluent chaining:

```php
Dokufy::driver('gotenberg')
    ->template(resource_path('templates/report.docx'))
    ->data(['title' => 'Monthly Report', 'date' => now()->format('F Y')])
    ->toPdf(storage_path('reports/monthly.pdf'));
```

## Resetting State

Reset the Dokufy instance to reuse it:

```php
$dokufy = Dokufy::make();

// First document
$dokufy->template($template1)->data($data1)->toPdf($output1);

// Reset and create second document
$dokufy->reset()
    ->template($template2)
    ->data($data2)
    ->toPdf($output2);
```

## Next Steps

- [Architecture](../02-architecture/README.md) - Understand how Dokufy works
- [Drivers](../03-drivers/README.md) - Learn about each driver's capabilities
- [API Reference](../04-api/README.md) - Complete method reference
