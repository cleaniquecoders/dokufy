# Architecture Overview

Dokufy is designed around the Driver Pattern, providing a unified API for multiple document generation backends.

## Design Philosophy

- **One API, Many Backends**: Write code once, swap implementations via config
- **Zero Lock-in**: Switch between Docker, CLI, or native PHP solutions
- **Laravel Native**: Integrates with Laravel's service container and configuration
- **Testable**: Built-in fake driver for testing without external dependencies

## Component Diagram

```text
┌─────────────────────────────────────────────────────────┐
│                      Dokufy Facade                       │
│                    (Unified API Layer)                   │
└───────────────────────────┬─────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────┐
│                      Dokufy Class                        │
│  - template() / html()   - data() / with()              │
│  - toPdf() / toDocx()    - stream() / download()        │
│  - driver() / make()     - getAvailableDrivers()        │
└───────────────────────────┬─────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│   Converter   │   │    Driver     │   │ Template      │
│   Contract    │   │   Contract    │   │ Processor     │
└───────┬───────┘   └───────┬───────┘   └───────────────┘
        │                   │
        └─────────┬─────────┘
                  │
    ┌─────────────┼─────────────┬─────────────┬─────────────┐
    │             │             │             │             │
    ▼             ▼             ▼             ▼             ▼
┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐
│Gotenberg│  │Libre   │  │Chromium│  │PHPWord │  │ Fake   │
│Driver   │  │Office  │  │Driver  │  │Driver  │  │Driver  │
│         │  │Driver  │  │        │  │        │  │        │
└────────┘  └────────┘  └────────┘  └────────┘  └────────┘
    │             │             │             │             │
    ▼             ▼             ▼             ▼             ▼
 Docker        CLI           Node.js       Native PHP    In-Memory
 Gotenberg   LibreOffice    Puppeteer     PHPWord+PDF    (Testing)
```

## Core Components

### Dokufy Class

The main orchestrator (`src/Dokufy.php`) that:

- Accepts input (templates or HTML)
- Binds data to placeholders
- Delegates conversion to the active driver
- Returns output (file path or HTTP response)

### Contracts

Interfaces that define the API for drivers:

- **Driver** (`src/Contracts/Driver.php`): Base interface extending Converter
- **Converter** (`src/Contracts/Converter.php`): PDF conversion methods
- **TemplateProcessor** (`src/Contracts/TemplateProcessor.php`): Template manipulation

### Drivers

Concrete implementations for each backend:

| Driver | Location | Backend |
|--------|----------|---------|
| GotenbergDriver | `src/Drivers/GotenbergDriver.php` | Gotenberg Docker API |
| LibreOfficeDriver | `src/Drivers/LibreOfficeDriver.php` | LibreOffice CLI |
| ChromiumDriver | `src/Drivers/ChromiumDriver.php` | Spatie Browsershot |
| PhpWordDriver | `src/Drivers/PhpWordDriver.php` | PHPWord + DomPDF |
| FakeDriver | `src/Drivers/FakeDriver.php` | In-memory (testing) |

### Service Provider

The `DokufyServiceProvider` (`src/DokufyServiceProvider.php`):

- Registers drivers as singletons in the container
- Publishes configuration file
- Sets up the Facade alias

### Exceptions

Custom exceptions for error handling:

- `DokufyException`: Base exception class
- `DriverException`: Driver not found/available/configured
- `ConversionException`: Conversion process failed
- `TemplateNotFoundException`: Template file not found

## Directory Structure

```text
src/
├── Dokufy.php                    # Main orchestrator
├── DokufyServiceProvider.php     # Laravel service provider
├── Facades/
│   └── Dokufy.php                # Laravel facade
├── Contracts/
│   ├── Driver.php                # Base driver contract
│   ├── Converter.php             # Conversion contract
│   └── TemplateProcessor.php     # Template contract
├── Drivers/
│   ├── GotenbergDriver.php       # Docker-based
│   ├── LibreOfficeDriver.php     # CLI-based
│   ├── ChromiumDriver.php        # Node.js-based
│   ├── PhpWordDriver.php         # Native PHP
│   └── FakeDriver.php            # Testing
├── Concerns/
│   └── InteractsWithPlaceholdify.php
└── Exceptions/
    ├── DokufyException.php
    ├── DriverException.php
    ├── ConversionException.php
    └── TemplateNotFoundException.php
```

## Next Steps

- [Driver Pattern](02-driver-pattern.md) - How drivers are resolved
- [Processing Flow](03-processing-flow.md) - Request lifecycle
