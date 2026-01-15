# LibreOffice Driver

The LibreOffice driver uses LibreOffice in headless mode for document conversion.
It's ideal for traditional server environments without Docker.

## Requirements

- LibreOffice installed on the system
- Write access to temporary directory

## Installation

### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install libreoffice
```

### CentOS/RHEL

```bash
sudo yum install libreoffice
```

### macOS

```bash
brew install --cask libreoffice
```

### Windows

Download and install from [libreoffice.org](https://www.libreoffice.org/download/download/).

## Configuration

```php
// config/dokufy.php
'drivers' => [
    'libreoffice' => [
        'binary' => env('DOKUFY_LIBREOFFICE_BINARY', 'libreoffice'),
        'timeout' => env('DOKUFY_LIBREOFFICE_TIMEOUT', 120),
    ],
],
```

### Environment Variables

```bash
# .env
DOKUFY_DRIVER=libreoffice
DOKUFY_LIBREOFFICE_BINARY=/usr/bin/libreoffice
DOKUFY_LIBREOFFICE_TIMEOUT=120
```

### Custom Binary Path

If LibreOffice is installed in a non-standard location:

```bash
# macOS
DOKUFY_LIBREOFFICE_BINARY=/Applications/LibreOffice.app/Contents/MacOS/soffice

# Windows
DOKUFY_LIBREOFFICE_BINARY="C:\Program Files\LibreOffice\program\soffice.exe"
```

## Supported Formats

| Input | Output |
|-------|--------|
| HTML | PDF |
| DOCX | PDF |
| DOC | PDF |
| XLSX | PDF |
| XLS | PDF |
| PPTX | PDF |
| PPT | PDF |
| ODT | PDF |
| ODS | PDF |
| ODP | PDF |
| RTF | PDF |

## Usage Examples

### DOCX to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::driver('libreoffice')
    ->template(resource_path('templates/report.docx'))
    ->data(['title' => 'Annual Report', 'year' => '2025'])
    ->toPdf(storage_path('reports/annual-2025.pdf'));
```

### HTML to PDF

```php
Dokufy::driver('libreoffice')
    ->html('<h1>Document Title</h1><p>Content here.</p>')
    ->toPdf(storage_path('documents/output.pdf'));
```

### Excel to PDF

```php
Dokufy::driver('libreoffice')
    ->template(resource_path('templates/spreadsheet.xlsx'))
    ->toPdf(storage_path('spreadsheets/output.pdf'));
```

## How It Works

The driver executes LibreOffice in headless mode:

```bash
libreoffice --headless --convert-to pdf --outdir /tmp /path/to/input.docx
```

### Process Flow

1. Write input content to a temporary file
2. Execute LibreOffice with `--headless --convert-to pdf`
3. Wait for conversion to complete
4. Move output file to destination
5. Clean up temporary files

## Performance Considerations

### Cold Start

LibreOffice has a significant cold start time (~2 seconds). For high-volume scenarios, consider:

- Using Gotenberg (keeps LibreOffice warm)
- Implementing a queue for batch processing
- Pre-warming with a dummy conversion on deployment

### Concurrent Conversions

LibreOffice can struggle with concurrent processes. Implement locking:

```php
use Illuminate\Support\Facades\Cache;

function convertWithLock(string $template, array $data, string $output): string
{
    $lock = Cache::lock('libreoffice-conversion', 120);

    try {
        $lock->block(60); // Wait up to 60 seconds

        return Dokufy::driver('libreoffice')
            ->template($template)
            ->data($data)
            ->toPdf($output);
    } finally {
        $lock->release();
    }
}
```

### Memory Usage

LibreOffice can consume significant memory. Monitor usage:

```bash
# Check memory usage during conversion
ps aux | grep soffice
```

## Troubleshooting

### Binary Not Found

```text
Error: LibreOffice binary not found at 'libreoffice'
```

**Solution**: Find and configure the correct path:

```bash
# Find LibreOffice
which libreoffice
# or
find / -name "soffice" 2>/dev/null
```

### Permission Denied

```text
Error: Permission denied executing libreoffice
```

**Solution**: Ensure the web server user can execute LibreOffice:

```bash
sudo chmod +x /usr/bin/libreoffice
```

### Conversion Fails Silently

LibreOffice may fail without clear errors. Check:

```bash
# Test manually
libreoffice --headless --convert-to pdf --outdir /tmp /path/to/file.docx

# Check for error output
ls -la /tmp/*.pdf
```

### Font Issues

Missing fonts cause incorrect rendering. Install common fonts:

```bash
# Ubuntu/Debian
sudo apt-get install fonts-liberation fonts-dejavu ttf-mscorefonts-installer

# CentOS/RHEL
sudo yum install liberation-fonts dejavu-fonts
```

## Checking Availability

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

if (Dokufy::isDriverAvailable('libreoffice')) {
    // LibreOffice is installed and accessible
}
```

## Next Steps

- [Chromium Driver](04-chromium.md) - For pixel-perfect HTML
- [PHPWord Driver](05-phpword.md) - For shared hosting
- [Configuration](../05-configuration/README.md) - All configuration options
