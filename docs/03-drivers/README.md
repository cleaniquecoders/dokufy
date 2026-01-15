# Drivers

This section documents each driver available in Dokufy, including setup
requirements, capabilities, and best use cases.

## Overview

Dokufy uses a driver-based architecture that allows you to switch between
different document conversion backends without changing your application code.
Each driver has its own strengths and requirements.

## Table of Contents

### [1. Overview](01-overview.md)

Comparison of all drivers, their capabilities, and how to choose the right one.

### [2. Gotenberg Driver](02-gotenberg.md)

Docker-based conversion using the Gotenberg API. Best for production and CI/CD.

### [3. LibreOffice Driver](03-libreoffice.md)

CLI-based conversion using LibreOffice headless mode. Best for traditional servers.

### [4. Chromium Driver](04-chromium.md)

Node.js-based HTML rendering using Puppeteer/Browsershot. Best for pixel-perfect HTML.

### [5. PHPWord Driver](05-phpword.md)

Native PHP conversion using PHPWord with DomPDF/TCPDF. Best for shared hosting.

### [6. Fake Driver](06-fake.md)

In-memory testing driver with assertions. Best for unit and feature tests.

## Quick Comparison

| Driver | Requires | Formats | Best For |
|--------|----------|---------|----------|
| Gotenberg | Docker | HTML, DOCX, XLSX, PPTX, ODT, MD | Production, CI/CD |
| LibreOffice | LibreOffice binary | HTML, DOCX, XLSX, PPTX, ODT | VPS, traditional servers |
| Chromium | Node.js, Puppeteer | HTML only | Pixel-perfect HTML rendering |
| PHPWord | Nothing (pure PHP) | DOCX only | Shared hosting |
| Fake | Nothing | All (mocked) | Testing |

## Related Documentation

- [Architecture](../02-architecture/README.md) - How drivers fit into the system
- [Configuration](../05-configuration/README.md) - Driver configuration options
- [Testing](../07-testing/README.md) - Using the Fake driver for tests
