# Install Command

The `dokufy:install` command provides an interactive setup wizard for
configuring Dokufy in your Laravel application.

## Usage

```bash
php artisan dokufy:install [options]
```

## Options

| Option             | Description                         |
|--------------------|-------------------------------------|
| `--force`          | Overwrite existing configuration    |
| `--no-interaction` | Run without interactive prompts     |

## What It Does

The install command performs the following steps:

1. **Publishes Configuration** - Copies `dokufy.php` to `config/`
2. **Creates Templates Directory** - Creates `resources/templates/`
3. **Creates Sample Template** - Adds a sample HTML template
4. **Checks Drivers** - Displays which drivers are available
5. **Offers Package Installation** - Optionally installs missing packages
6. **Shows Next Steps** - Provides guidance for completing setup

## Example Output

```text
INFO  Installing Dokufy...

Config ................................ Published
Templates Directory ................... Created
Sample Template ....................... Created

INFO  Checking available drivers...

Gotenberg ............................. ✗ Not Available
Chromium .............................. ✗ Not Available
Phpword ............................... ✗ Not Available
Libreoffice ........................... ✓ Available

? Would you like to install additional drivers? (yes/no) [no]

INFO  Installation complete!

Next steps:

  1. Configure your preferred driver in config/dokufy.php
  2. Set environment variables for your chosen driver:

     # For Gotenberg (Docker)
     DOKUFY_DRIVER=gotenberg
     DOKUFY_GOTENBERG_URL=http://localhost:3000

     # For Chromium (Browsershot)
     DOKUFY_DRIVER=chromium

     # For LibreOffice
     DOKUFY_DRIVER=libreoffice

  3. Create your templates in resources/templates/
  4. Generate your first document:

     php artisan dokufy:generate resources/templates/sample.html output.pdf

  5. Check driver status anytime with:

     php artisan dokufy:status
```

## Sample Template

The install command creates a sample template at `resources/templates/sample.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body>
    <h1>{{ title }}</h1>
    <p>Hello, <strong>{{ name }}</strong>!</p>
    <p>{{ content }}</p>
    <footer>Generated on {{ date }} by Dokufy</footer>
</body>
</html>
```

## Installing Additional Drivers

When prompted, you can select additional drivers to install:

```text
? Would you like to install additional drivers? (yes/no) [no]
> yes

? Select drivers to install (Use space to select, enter to confirm)
  [ ] gotenberg (gotenberg/gotenberg-php) - Docker-based PDF generation
  [x] chromium (spatie/browsershot) - Browser-based PDF generation
  [ ] phpword (phpoffice/phpword) - Native PHP DOCX processing

INFO  Installing selected packages...

Installing spatie/browsershot ......... Done
```

## Non-Interactive Mode

For automated deployments, use `--no-interaction`:

```bash
php artisan dokufy:install --no-interaction
```

This will:

- Publish the configuration (skip if exists)
- Create the templates directory
- Create the sample template
- Skip the driver installation prompt

## Force Reinstall

To overwrite existing configuration:

```bash
php artisan dokufy:install --force
```

## Exit Codes

| Code | Meaning                             |
|------|-------------------------------------|
| 0    | Installation completed successfully |
| 1    | Installation failed                 |

## Post-Installation

After running the install command:

### 1. Configure Your Driver

Edit `config/dokufy.php`:

```php
return [
    'default' => env('DOKUFY_DRIVER', 'gotenberg'),
    // ...
];
```

### 2. Set Environment Variables

Add to your `.env` file:

```env
DOKUFY_DRIVER=gotenberg
DOKUFY_GOTENBERG_URL=http://localhost:3000
```

### 3. Verify Installation

```bash
php artisan dokufy:status
```

### 4. Generate Your First Document

```bash
php artisan dokufy:generate resources/templates/sample.html output.pdf \
    --data='{"title":"Welcome","name":"User","content":"Hello!","date":"2026-01-15"}'
```

## Related Commands

- [dokufy:status](01-status.md) - Check driver availability
- [dokufy:generate](02-generate.md) - Generate documents
