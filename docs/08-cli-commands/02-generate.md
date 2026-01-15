# Generate Command

The `dokufy:generate` command creates PDF or DOCX documents from templates
directly from the command line.

## Usage

```bash
php artisan dokufy:generate [input] [output] [options]
```

## Arguments

| Argument | Description                               | Required |
|----------|-------------------------------------------|----------|
| `input`  | Path to the input file (HTML or DOCX)     | No*      |
| `output` | Path for the generated output file        | No*      |

*If not provided, the command will prompt interactively.

## Options

| Option         | Description                                  |
|----------------|----------------------------------------------|
| `--driver=`    | Driver to use (gotenberg, libreoffice, etc.) |
| `--data=`      | JSON string of placeholder data              |
| `--data-file=` | Path to a JSON file with placeholder data    |
| `--force`      | Overwrite output file if it already exists   |

## Examples

### Basic HTML to PDF

```bash
php artisan dokufy:generate template.html output.pdf
```

### With Driver Selection

```bash
php artisan dokufy:generate template.html output.pdf --driver=chromium
```

### With Inline Data

```bash
php artisan dokufy:generate template.html output.pdf \
    --data='{"name":"John Doe","date":"2026-01-15"}'
```

### With Data File

Create a JSON data file:

```json
{
    "name": "John Doe",
    "company": "Acme Inc",
    "date": "2026-01-15",
    "total": "RM 1,500.00"
}
```

Then use it:

```bash
php artisan dokufy:generate invoice.html invoice.pdf --data-file=data.json
```

### Force Overwrite

```bash
php artisan dokufy:generate template.html output.pdf --force
```

### DOCX to PDF Conversion

```bash
php artisan dokufy:generate document.docx output.pdf --driver=libreoffice
```

### Generate DOCX from Template

```bash
php artisan dokufy:generate template.docx output.docx
```

## Interactive Mode

When running without arguments, the command enters interactive mode:

```bash
php artisan dokufy:generate

> Enter the input file path: resources/templates/invoice.html
> Select output format: PDF Document
> Enter the output file path: storage/app/invoice.pdf
```

## Output

On success, the command displays:

```text
INFO  Generating document...

SUCCESS  Document generated successfully: storage/app/invoice.pdf

Input ................................. resources/templates/invoice.html
Output ................................ storage/app/invoice.pdf
Size .................................. 125.50 KB
```

## Supported Formats

### Input Formats

- `.html`, `.htm` - HTML templates
- `.docx` - Word document templates

### Output Formats

- `.pdf` - PDF documents
- `.docx` - Word documents

## Exit Codes

| Code | Meaning                                |
|------|----------------------------------------|
| 0    | Document generated successfully        |
| 1    | Error (file not found, invalid driver) |

## Error Handling

### File Not Found

```bash
php artisan dokufy:generate missing.html output.pdf
# ERROR  Input file not found: missing.html
```

### Invalid Driver

```bash
php artisan dokufy:generate template.html output.pdf --driver=invalid
# ERROR  Driver [invalid] not found.
# INFO  Available drivers: fake, gotenberg, libreoffice, chromium, phpword
```

### Unsupported Format

```bash
php artisan dokufy:generate template.html output.xyz
# ERROR  Unsupported output format: xyz
```

## Use Cases

### Batch Processing with Shell Scripts

```bash
#!/bin/bash
for template in templates/*.html; do
    filename=$(basename "$template" .html)
    php artisan dokufy:generate "$template" "output/${filename}.pdf" --force
done
```

### CI/CD Document Generation

```bash
# Generate release notes PDF
php artisan dokufy:generate \
    docs/release-notes.html \
    dist/release-notes.pdf \
    --driver=fake \
    --force
```

### Scheduled Reports

In your Laravel scheduler:

```php
$schedule->exec('php artisan dokufy:generate ' .
    'resources/templates/daily-report.html ' .
    'storage/reports/report-' . date('Y-m-d') . '.pdf ' .
    '--data-file=storage/reports/data.json ' .
    '--force'
)->daily();
```

## Related Commands

- [dokufy:status](01-status.md) - Check driver availability
- [dokufy:install](03-install.md) - Set up Dokufy
