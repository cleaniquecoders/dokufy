# Chromium Driver

The Chromium driver uses [Browsershot](https://github.com/spatie/browsershot)
(Puppeteer) for HTML to PDF conversion. It provides pixel-perfect rendering of
HTML, including full CSS and JavaScript support.

## Requirements

- Node.js (14+)
- NPM
- Puppeteer (installed automatically by Browsershot)
- `spatie/browsershot` package

## Installation

Install the PHP package:

```bash
composer require spatie/browsershot
```

Install Puppeteer:

```bash
npm install puppeteer
```

Or let Browsershot install it automatically on first use.

## Configuration

```php
// config/dokufy.php
'drivers' => [
    'chromium' => [
        'node_binary' => env('DOKUFY_NODE_BINARY'),
        'npm_binary' => env('DOKUFY_NPM_BINARY'),
        'timeout' => env('DOKUFY_CHROMIUM_TIMEOUT', 60),
    ],
],
```

### Environment Variables

```bash
# .env
DOKUFY_DRIVER=chromium
DOKUFY_NODE_BINARY=/usr/bin/node
DOKUFY_NPM_BINARY=/usr/bin/npm
DOKUFY_CHROMIUM_TIMEOUT=60
```

### Custom Binary Paths

If Node.js is installed via NVM or in a non-standard location:

```bash
# NVM installation
DOKUFY_NODE_BINARY=/home/user/.nvm/versions/node/v18.17.0/bin/node
DOKUFY_NPM_BINARY=/home/user/.nvm/versions/node/v18.17.0/bin/npm
```

## Supported Formats

| Input | Output |
|-------|--------|
| HTML | PDF |

> **Note**: The Chromium driver only supports HTML input. For DOCX conversion, use Gotenberg or LibreOffice.

## Usage Examples

### Basic HTML to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::driver('chromium')
    ->html('<h1>Hello World</h1>')
    ->toPdf(storage_path('documents/hello.pdf'));
```

### Blade View with Tailwind CSS

```php
$html = view('invoices.template', [
    'invoice' => $invoice,
    'customer' => $customer,
    'items' => $items,
])->render();

Dokufy::driver('chromium')
    ->html($html)
    ->toPdf(storage_path("invoices/{$invoice->number}.pdf"));
```

### Complex HTML Document

```php
$html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; }
        .header { background: #3490dc; color: white; padding: 20px; }
        .content { padding: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice #12345</h1>
    </div>
    <div class="content">
        <table>
            <tr><th>Item</th><th>Quantity</th><th>Price</th></tr>
            <tr><td>Widget</td><td>10</td><td>$100.00</td></tr>
        </table>
    </div>
</body>
</html>
HTML;

Dokufy::driver('chromium')
    ->html($html)
    ->toPdf(storage_path('invoices/12345.pdf'));
```

## Best Use Cases

The Chromium driver excels at:

- **Modern CSS**: Flexbox, Grid, CSS Variables
- **Tailwind CSS**: Full utility class support
- **Web Fonts**: Google Fonts, custom @font-face
- **Complex Layouts**: Multi-column, responsive designs
- **JavaScript**: Dynamic content generation
- **SVG Graphics**: Charts, icons, diagrams

## Limitations

- **HTML only**: Cannot convert DOCX, XLSX, or other formats
- **Resource intensive**: Spawns a full browser process
- **Slower**: More overhead than native converters
- **No template processing**: Must pre-process placeholders

## Performance Tips

### Disable JavaScript When Not Needed

If your HTML doesn't require JavaScript:

```php
// This is handled internally by Browsershot configuration
// JS is disabled by default for faster rendering
```

### Use System Fonts

Custom fonts require additional download time. Use system fonts when possible:

```css
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
```

### Inline CSS

External stylesheets require additional network requests. Inline CSS for faster rendering:

```php
$css = file_get_contents(public_path('css/invoice.css'));
$html = "<style>{$css}</style>" . $content;
```

## Troubleshooting

### Puppeteer Not Found

```text
Error: Could not find Puppeteer
```

**Solution**: Install Puppeteer globally or in your project:

```bash
npm install puppeteer
# or globally
npm install -g puppeteer
```

### Chrome/Chromium Not Found

```text
Error: Failed to launch chrome
```

**Solution**: Install Chromium or set the executable path:

```bash
# Ubuntu/Debian
sudo apt-get install chromium-browser

# Or install via Puppeteer
npx puppeteer browsers install chrome
```

### Sandbox Issues on Linux

```text
Error: Running as root without --no-sandbox is not supported
```

**Solution**: Configure Browsershot to disable sandbox (in production, consider using a proper sandbox setup):

```php
// This is typically handled by the driver configuration
```

### Timeout Errors

```text
Error: Navigation timeout of 30000 ms exceeded
```

**Solution**: Increase timeout in configuration:

```bash
DOKUFY_CHROMIUM_TIMEOUT=120
```

### Missing Fonts

Fonts appear incorrect or as boxes.

**Solution**: Install fonts on the server:

```bash
# Ubuntu/Debian
sudo apt-get install fonts-liberation fonts-noto

# Or embed fonts in your HTML
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
```

## Docker Configuration

For Docker environments, install Chrome dependencies:

```dockerfile
FROM php:8.2-fpm

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install Chrome dependencies
RUN apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libasound2

# Install Puppeteer
RUN npm install -g puppeteer
```

## Checking Availability

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

if (Dokufy::isDriverAvailable('chromium')) {
    // Chromium/Browsershot is ready
}
```

## Next Steps

- [PHPWord Driver](05-phpword.md) - Pure PHP alternative
- [Fake Driver](06-fake.md) - For testing
- [Examples](../06-examples/README.md) - Real-world examples
