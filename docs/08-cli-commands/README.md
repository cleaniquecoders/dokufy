# CLI Commands

Dokufy provides Artisan commands for common document generation tasks
and setup operations.

## Overview

The CLI commands allow you to:

- Check driver availability and configuration status
- Generate documents directly from the command line
- Set up Dokufy with an interactive installation wizard

## Table of Contents

### [1. Status Command](01-status.md)

Check the availability and configuration status of all Dokufy drivers.

### [2. Generate Command](02-generate.md)

Generate PDF or DOCX documents from templates via the command line.

### [3. Install Command](03-install.md)

Interactive setup wizard for configuring Dokufy in your application.

## Quick Reference

```bash
# Check driver status
php artisan dokufy:status

# Generate a document
php artisan dokufy:generate input.html output.pdf --driver=fake

# Run the installation wizard
php artisan dokufy:install
```

## Related Documentation

- [Getting Started](../01-getting-started/README.md) - Initial setup guide
- [Configuration](../05-configuration/README.md) - Configuration options
- [Drivers](../03-drivers/README.md) - Available drivers
