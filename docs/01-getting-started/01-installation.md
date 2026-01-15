# Installation

This guide covers installing Dokufy and its dependencies.

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x

### Driver-Specific Requirements

Depending on which driver you plan to use, you may need additional dependencies:

| Driver | Requirements |
|--------|--------------|
| Gotenberg | Docker with Gotenberg container |
| LibreOffice | LibreOffice binary installed locally |
| Chromium | Node.js and Puppeteer |
| PHPWord | No external dependencies |

## Install via Composer

```bash
composer require cleaniquecoders/dokufy
```

## Publish Configuration

Publish the configuration file to customize Dokufy's behavior:

```bash
php artisan vendor:publish --tag="dokufy-config"
```

This creates `config/dokufy.php` with all available options.

## Install Driver Dependencies

Install the dependencies for your chosen driver.

### Gotenberg Driver

```bash
composer require gotenberg/gotenberg-php
```

Start the Gotenberg container:

```bash
docker run -d -p 3000:3000 gotenberg/gotenberg:8
```

### LibreOffice Driver

No additional PHP packages needed. Install LibreOffice on your system:

```bash
# Ubuntu/Debian
sudo apt-get install libreoffice

# macOS
brew install --cask libreoffice

# Or download from https://www.libreoffice.org/
```

### Chromium Driver

```bash
composer require spatie/browsershot
```

Install Puppeteer:

```bash
npm install puppeteer
```

### PHPWord Driver

```bash
composer require phpoffice/phpword dompdf/dompdf
```

You can use `tcpdf/tcpdf` or `mpdf/mpdf` as alternative PDF renderers.

## Verify Installation

Test that Dokufy is working:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

// Check available drivers
$drivers = Dokufy::getAvailableDrivers();
dd($drivers);

// Check if your preferred driver is available
$available = Dokufy::isDriverAvailable('phpword');
dd($available); // true or false
```

## Next Steps

- [Quick Start](02-quick-start.md) - Generate your first PDF
- [Basic Usage](03-basic-usage.md) - Learn the core API
