# Configuration File

The main configuration file is located at `config/dokufy.php` after publishing.

## Publishing Configuration

```bash
php artisan vendor:publish --tag=dokufy-config
```

## Complete Configuration

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default document conversion driver that will
    | be used by the package. You may set this to any of the drivers
    | defined in the "drivers" array below.
    |
    | Supported: "gotenberg", "libreoffice", "chromium", "phpword", "fake"
    |
    */
    'default' => env('DOKUFY_DRIVER', 'gotenberg'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure each driver supported by Dokufy. Each driver
    | has its own set of options that control its behavior.
    |
    */
    'drivers' => [
        'gotenberg' => [
            'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
            'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
        ],

        'libreoffice' => [
            'binary' => env('DOKUFY_LIBREOFFICE_BINARY', 'libreoffice'),
            'timeout' => env('DOKUFY_LIBREOFFICE_TIMEOUT', 120),
        ],

        'chromium' => [
            'node_binary' => env('DOKUFY_NODE_BINARY'),
            'npm_binary' => env('DOKUFY_NPM_BINARY'),
            'timeout' => env('DOKUFY_CHROMIUM_TIMEOUT', 60),
        ],

        'phpword' => [
            'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Options
    |--------------------------------------------------------------------------
    |
    | These options control the default PDF output settings. These can be
    | overridden per-request when generating documents.
    |
    */
    'pdf' => [
        'format' => env('DOKUFY_PDF_FORMAT', 'A4'),
        'orientation' => env('DOKUFY_PDF_ORIENTATION', 'portrait'),
        'margin_top' => env('DOKUFY_PDF_MARGIN_TOP', '1in'),
        'margin_bottom' => env('DOKUFY_PDF_MARGIN_BOTTOM', '1in'),
        'margin_left' => env('DOKUFY_PDF_MARGIN_LEFT', '0.5in'),
        'margin_right' => env('DOKUFY_PDF_MARGIN_RIGHT', '0.5in'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | The default path where Dokufy will look for template files. This can
    | be any directory accessible by your application.
    |
    */
    'templates' => [
        'path' => env('DOKUFY_TEMPLATES_PATH', resource_path('templates')),
    ],
];
```

## Configuration Sections

### Default Driver

The `default` key determines which driver is used when no driver is explicitly specified:

```php
'default' => env('DOKUFY_DRIVER', 'gotenberg'),
```

Valid values:

- `gotenberg` - Docker-based, requires Gotenberg container
- `libreoffice` - CLI-based, requires LibreOffice installed
- `chromium` - Node.js-based, requires Puppeteer
- `phpword` - Pure PHP, no external dependencies
- `fake` - Testing driver

### Driver Configurations

Each driver has its own configuration section:

#### Gotenberg

```php
'gotenberg' => [
    'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
    'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
],
```

- `url`: Gotenberg API endpoint
- `timeout`: Request timeout in seconds

#### LibreOffice

```php
'libreoffice' => [
    'binary' => env('DOKUFY_LIBREOFFICE_BINARY', 'libreoffice'),
    'timeout' => env('DOKUFY_LIBREOFFICE_TIMEOUT', 120),
],
```

- `binary`: Path to LibreOffice executable
- `timeout`: Conversion timeout in seconds

#### Chromium

```php
'chromium' => [
    'node_binary' => env('DOKUFY_NODE_BINARY'),
    'npm_binary' => env('DOKUFY_NPM_BINARY'),
    'timeout' => env('DOKUFY_CHROMIUM_TIMEOUT', 60),
],
```

- `node_binary`: Path to Node.js binary (optional, auto-detected)
- `npm_binary`: Path to NPM binary (optional, auto-detected)
- `timeout`: Rendering timeout in seconds

#### PHPWord

```php
'phpword' => [
    'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'),
],
```

- `pdf_renderer`: PDF rendering library (`dompdf`, `tcpdf`, `mpdf`)

### PDF Options

Global PDF settings applied to all conversions:

```php
'pdf' => [
    'format' => 'A4',           // Paper size
    'orientation' => 'portrait', // portrait or landscape
    'margin_top' => '1in',      // Top margin
    'margin_bottom' => '1in',   // Bottom margin
    'margin_left' => '0.5in',   // Left margin
    'margin_right' => '0.5in',  // Right margin
],
```

### Template Paths

Default directory for template files:

```php
'templates' => [
    'path' => resource_path('templates'),
],
```

## Accessing Configuration

```php
// Get default driver
$driver = config('dokufy.default');

// Get Gotenberg URL
$url = config('dokufy.drivers.gotenberg.url');

// Get PDF format
$format = config('dokufy.pdf.format');

// Get templates path
$path = config('dokufy.templates.path');
```

## Runtime Configuration

Override configuration at runtime:

```php
config(['dokufy.default' => 'libreoffice']);

// Or set driver per-request
Dokufy::driver('libreoffice')->html($html)->toPdf($output);
```

## Next Steps

- [Environment Variables](02-environment.md) - All env vars
- [PDF Options](03-pdf-options.md) - Paper and margin settings
