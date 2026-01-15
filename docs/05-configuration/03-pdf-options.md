# PDF Options

Configure paper size, orientation, margins, and other PDF settings.

## Default Settings

```php
// config/dokufy.php
'pdf' => [
    'format' => env('DOKUFY_PDF_FORMAT', 'A4'),
    'orientation' => env('DOKUFY_PDF_ORIENTATION', 'portrait'),
    'margin_top' => env('DOKUFY_PDF_MARGIN_TOP', '1in'),
    'margin_bottom' => env('DOKUFY_PDF_MARGIN_BOTTOM', '1in'),
    'margin_left' => env('DOKUFY_PDF_MARGIN_LEFT', '0.5in'),
    'margin_right' => env('DOKUFY_PDF_MARGIN_RIGHT', '0.5in'),
],
```

## Paper Formats

### Standard Sizes

| Format | Dimensions (mm) | Dimensions (inches) |
|--------|-----------------|---------------------|
| A3 | 297 x 420 | 11.69 x 16.54 |
| A4 | 210 x 297 | 8.27 x 11.69 |
| A5 | 148 x 210 | 5.83 x 8.27 |
| Letter | 216 x 279 | 8.5 x 11 |
| Legal | 216 x 356 | 8.5 x 14 |
| Tabloid | 279 x 432 | 11 x 17 |

### Setting Paper Format

```bash
# .env
DOKUFY_PDF_FORMAT=A4
```

### Common Use Cases

| Document Type | Recommended Format |
|---------------|-------------------|
| Letters, Reports | A4 or Letter |
| Legal Documents | Legal |
| Brochures | A5 |
| Posters | A3 or Tabloid |
| Receipts | A5 |

## Page Orientation

### Portrait

Taller than wide. Default for most documents.

```bash
DOKUFY_PDF_ORIENTATION=portrait
```

### Landscape

Wider than tall. Good for tables, charts, and presentations.

```bash
DOKUFY_PDF_ORIENTATION=landscape
```

### When to Use Landscape

- Wide tables that don't fit in portrait
- Charts and graphs
- Certificates and awards
- Presentation slides

## Margins

### Margin Units

Margins can be specified in various CSS units:

| Unit | Example | Description |
|------|---------|-------------|
| `in` | `1in` | Inches |
| `cm` | `2.54cm` | Centimeters |
| `mm` | `25.4mm` | Millimeters |
| `px` | `96px` | Pixels (96 dpi) |

### Standard Margins

```bash
# Standard letter/report margins
DOKUFY_PDF_MARGIN_TOP=1in
DOKUFY_PDF_MARGIN_BOTTOM=1in
DOKUFY_PDF_MARGIN_LEFT=1in
DOKUFY_PDF_MARGIN_RIGHT=1in

# Narrow margins (more content)
DOKUFY_PDF_MARGIN_TOP=0.5in
DOKUFY_PDF_MARGIN_BOTTOM=0.5in
DOKUFY_PDF_MARGIN_LEFT=0.5in
DOKUFY_PDF_MARGIN_RIGHT=0.5in

# Wide margins (formal documents)
DOKUFY_PDF_MARGIN_TOP=1.5in
DOKUFY_PDF_MARGIN_BOTTOM=1.5in
DOKUFY_PDF_MARGIN_LEFT=1.25in
DOKUFY_PDF_MARGIN_RIGHT=1.25in
```

### Asymmetric Margins

For bound documents, use wider inner margins:

```bash
# Left-bound document
DOKUFY_PDF_MARGIN_LEFT=1.5in
DOKUFY_PDF_MARGIN_RIGHT=1in
```

## Margin Guidelines

### By Document Type

| Document Type | Top | Bottom | Left | Right |
|---------------|-----|--------|------|-------|
| Business Letter | 1in | 1in | 1in | 1in |
| Report | 1in | 1in | 1.25in | 1.25in |
| Invoice | 0.5in | 0.5in | 0.5in | 0.5in |
| Legal Document | 1in | 1in | 1.5in | 1in |
| Certificate | 0.75in | 0.75in | 0.75in | 0.75in |

### For Printing

Consider printer limitations:

- Most printers cannot print to the edge
- Typical minimum margin: 0.25in (6mm)
- Safe margin for all printers: 0.5in (12mm)

## Driver-Specific Options

### Gotenberg Options

Gotenberg supports additional PDF options via the API. These can be configured in the driver configuration:

```php
// config/dokufy.php
'drivers' => [
    'gotenberg' => [
        'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
        'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
        'options' => [
            'preferCssPageSize' => false,
            'printBackground' => true,
            'scale' => 1.0,
        ],
    ],
],
```

### Chromium Options

Browsershot (Chromium driver) supports many options:

```php
'chromium' => [
    'timeout' => 60,
    'options' => [
        'printBackground' => true,
        'displayHeaderFooter' => false,
        'scale' => 1,
    ],
],
```

### PHPWord Options

PHPWord PDF rendering options:

```php
'phpword' => [
    'pdf_renderer' => 'dompdf',
    'options' => [
        'defaultPaperSize' => 'A4',
        'defaultFont' => 'Arial',
    ],
],
```

## Common Configurations

### Invoice Template

```bash
# Compact, professional
DOKUFY_PDF_FORMAT=A4
DOKUFY_PDF_ORIENTATION=portrait
DOKUFY_PDF_MARGIN_TOP=0.5in
DOKUFY_PDF_MARGIN_BOTTOM=0.5in
DOKUFY_PDF_MARGIN_LEFT=0.5in
DOKUFY_PDF_MARGIN_RIGHT=0.5in
```

### Formal Letter

```bash
# Traditional letter format
DOKUFY_PDF_FORMAT=Letter
DOKUFY_PDF_ORIENTATION=portrait
DOKUFY_PDF_MARGIN_TOP=1in
DOKUFY_PDF_MARGIN_BOTTOM=1in
DOKUFY_PDF_MARGIN_LEFT=1in
DOKUFY_PDF_MARGIN_RIGHT=1in
```

### Financial Report

```bash
# Wide tables
DOKUFY_PDF_FORMAT=A4
DOKUFY_PDF_ORIENTATION=landscape
DOKUFY_PDF_MARGIN_TOP=0.75in
DOKUFY_PDF_MARGIN_BOTTOM=0.75in
DOKUFY_PDF_MARGIN_LEFT=0.5in
DOKUFY_PDF_MARGIN_RIGHT=0.5in
```

### Certificate

```bash
# Landscape with balanced margins
DOKUFY_PDF_FORMAT=A4
DOKUFY_PDF_ORIENTATION=landscape
DOKUFY_PDF_MARGIN_TOP=0.75in
DOKUFY_PDF_MARGIN_BOTTOM=0.75in
DOKUFY_PDF_MARGIN_LEFT=0.75in
DOKUFY_PDF_MARGIN_RIGHT=0.75in
```

## Next Steps

- [Examples](../06-examples/README.md) - Real-world configurations
- [Drivers](../03-drivers/README.md) - Driver-specific settings
