# Configuration

Complete configuration reference for Dokufy.

## Overview

Dokufy is configured through the `config/dokufy.php` file. This section covers
all available options, environment variables, and customization points.

## Table of Contents

### [1. Configuration File](01-config-file.md)

Complete reference for the `config/dokufy.php` file structure.

### [2. Environment Variables](02-environment.md)

All environment variables and their defaults.

### [3. PDF Options](03-pdf-options.md)

Paper size, orientation, margins, and other PDF settings.

## Quick Setup

Publish the configuration file:

```bash
php artisan vendor:publish --tag=dokufy-config
```

Set the driver in your `.env`:

```bash
DOKUFY_DRIVER=gotenberg
```

## Related Documentation

- [Drivers](../03-drivers/README.md) - Driver-specific configuration
- [Getting Started](../01-getting-started/README.md) - Initial setup
