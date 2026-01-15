# Getting Started

This section covers everything you need to get up and running with Dokufy.

## Overview

Dokufy provides a unified API for document generation and PDF conversion in
Laravel applications. You write your code once and can switch between different
backends (Gotenberg, LibreOffice, Chromium, PHPWord) through configuration.

## Table of Contents

### [1. Installation](01-installation.md)

Requirements, installation via Composer, and publishing configuration.

### [2. Quick Start](02-quick-start.md)

Generate your first PDF in under 5 minutes.

### [3. Basic Usage](03-basic-usage.md)

Core concepts: templates, HTML content, data binding, and output methods.

## Interactive Setup

For a guided installation experience, use the install command:

```bash
php artisan dokufy:install
```

This interactive wizard will:

- Publish the configuration file
- Create the templates directory
- Check available drivers
- Offer to install missing driver packages

See [CLI Commands](../08-cli-commands/03-install.md) for more details.

## Related Documentation

- [Architecture Overview](../02-architecture/README.md) - How Dokufy works
- [Drivers](../03-drivers/README.md) - Choose the right driver for your needs
- [Configuration](../05-configuration/README.md) - Configure your preferred driver
- [CLI Commands](../08-cli-commands/README.md) - Artisan commands reference
