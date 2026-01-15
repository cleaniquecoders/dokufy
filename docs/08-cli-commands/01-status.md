# Status Command

The `dokufy:status` command displays the availability and configuration
status of all Dokufy drivers.

## Usage

```bash
php artisan dokufy:status
```

## Output

The command displays a table with all available drivers and their status:

```text
Dokufy Driver Status

+------------+-------------+----------------+------------------------+
| Driver     | Key         | Status         | Package                |
+------------+-------------+----------------+------------------------+
| Gotenberg  | gotenberg   | ✗ Not Available| gotenberg/gotenberg-php|
| LibreOffice| libreoffice | ✓ Available    | -                      |
| Chromium   | chromium    | ✗ Not Available| spatie/browsershot     |
| PHPWord    | phpword     | ✗ Not Available| phpoffice/phpword      |
| Fake       | fake        | ✓ Available    | -                      |
+------------+-------------+----------------+------------------------+

Default Driver ............................ gotenberg
Available Drivers ......................... 2/5

Dokufy is ready to use!
```

## Status Information

The command shows:

- **Driver Name**: Human-readable driver name
- **Key**: Configuration key used in `config/dokufy.php`
- **Status**: Whether the driver is available for use
- **Package**: Required Composer package (if any)

## Exit Codes

| Code | Meaning                                     |
|------|---------------------------------------------|
| 0    | Success - driver available and ready        |
| 1    | Failure - no drivers or default unavailable |

## Use Cases

### Verify Installation

After installing Dokufy, run the status command to verify which drivers
are available:

```bash
php artisan dokufy:status
```

### Troubleshoot Configuration

If document generation fails, check driver status to ensure the configured
driver is available:

```bash
# Check status
php artisan dokufy:status

# If default driver shows "Not Available", either:
# 1. Install the required package
# 2. Change the default driver in config/dokufy.php
```

### CI/CD Pipeline

Use the exit code in CI/CD pipelines to verify driver availability:

```bash
php artisan dokufy:status || echo "Dokufy not properly configured"
```

## Related Commands

- [dokufy:install](03-install.md) - Set up Dokufy interactively
- [dokufy:generate](02-generate.md) - Generate documents from CLI
