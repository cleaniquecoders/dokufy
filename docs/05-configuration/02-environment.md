# Environment Variables

Complete list of environment variables for configuring Dokufy.

## Quick Reference

```bash
# Driver Selection
DOKUFY_DRIVER=gotenberg

# Gotenberg Driver
DOKUFY_GOTENBERG_URL=http://localhost:3000
DOKUFY_GOTENBERG_TIMEOUT=120

# LibreOffice Driver
DOKUFY_LIBREOFFICE_BINARY=libreoffice
DOKUFY_LIBREOFFICE_TIMEOUT=120

# Chromium Driver
DOKUFY_NODE_BINARY=/usr/bin/node
DOKUFY_NPM_BINARY=/usr/bin/npm
DOKUFY_CHROMIUM_TIMEOUT=60

# PHPWord Driver
DOKUFY_PDF_RENDERER=dompdf

# PDF Options
DOKUFY_PDF_FORMAT=A4
DOKUFY_PDF_ORIENTATION=portrait
DOKUFY_PDF_MARGIN_TOP=1in
DOKUFY_PDF_MARGIN_BOTTOM=1in
DOKUFY_PDF_MARGIN_LEFT=0.5in
DOKUFY_PDF_MARGIN_RIGHT=0.5in

# Templates
DOKUFY_TEMPLATES_PATH=
```

## Driver Selection

### DOKUFY_DRIVER

Default driver for document conversion.

| Property | Value |
|----------|-------|
| Default | `gotenberg` |
| Options | `gotenberg`, `libreoffice`, `chromium`, `phpword`, `fake` |

```bash
DOKUFY_DRIVER=gotenberg
```

## Gotenberg Driver

### DOKUFY_GOTENBERG_URL

URL of the Gotenberg API endpoint.

| Property | Value |
|----------|-------|
| Default | `http://gotenberg:3000` |
| Type | URL |

```bash
# Docker Compose (internal network)
DOKUFY_GOTENBERG_URL=http://gotenberg:3000

# Local development
DOKUFY_GOTENBERG_URL=http://localhost:3000

# Kubernetes
DOKUFY_GOTENBERG_URL=http://gotenberg.default.svc.cluster.local:3000
```

### DOKUFY_GOTENBERG_TIMEOUT

Request timeout in seconds for Gotenberg API calls.

| Property | Value |
|----------|-------|
| Default | `120` |
| Type | Integer (seconds) |

```bash
# Standard documents
DOKUFY_GOTENBERG_TIMEOUT=120

# Large documents
DOKUFY_GOTENBERG_TIMEOUT=300
```

## LibreOffice Driver

### DOKUFY_LIBREOFFICE_BINARY

Path to the LibreOffice executable.

| Property | Value |
|----------|-------|
| Default | `libreoffice` |
| Type | Path |

```bash
# Default (uses PATH)
DOKUFY_LIBREOFFICE_BINARY=libreoffice

# Ubuntu/Debian
DOKUFY_LIBREOFFICE_BINARY=/usr/bin/libreoffice

# macOS
DOKUFY_LIBREOFFICE_BINARY=/Applications/LibreOffice.app/Contents/MacOS/soffice

# Windows
DOKUFY_LIBREOFFICE_BINARY="C:\Program Files\LibreOffice\program\soffice.exe"
```

### DOKUFY_LIBREOFFICE_TIMEOUT

Conversion timeout in seconds.

| Property | Value |
|----------|-------|
| Default | `120` |
| Type | Integer (seconds) |

```bash
DOKUFY_LIBREOFFICE_TIMEOUT=120
```

## Chromium Driver

### DOKUFY_NODE_BINARY

Path to the Node.js executable.

| Property | Value |
|----------|-------|
| Default | Auto-detected |
| Type | Path |

```bash
# Default (auto-detect)
DOKUFY_NODE_BINARY=

# Explicit path
DOKUFY_NODE_BINARY=/usr/bin/node

# NVM installation
DOKUFY_NODE_BINARY=/home/user/.nvm/versions/node/v18.17.0/bin/node
```

### DOKUFY_NPM_BINARY

Path to the NPM executable.

| Property | Value |
|----------|-------|
| Default | Auto-detected |
| Type | Path |

```bash
# Default (auto-detect)
DOKUFY_NPM_BINARY=

# Explicit path
DOKUFY_NPM_BINARY=/usr/bin/npm
```

### DOKUFY_CHROMIUM_TIMEOUT

Rendering timeout in seconds.

| Property | Value |
|----------|-------|
| Default | `60` |
| Type | Integer (seconds) |

```bash
DOKUFY_CHROMIUM_TIMEOUT=60
```

## PHPWord Driver

### DOKUFY_PDF_RENDERER

PDF rendering library for PHPWord.

| Property | Value |
|----------|-------|
| Default | `dompdf` |
| Options | `dompdf`, `tcpdf`, `mpdf` |

```bash
# DomPDF (simple, fast)
DOKUFY_PDF_RENDERER=dompdf

# TCPDF (complex layouts)
DOKUFY_PDF_RENDERER=tcpdf

# mPDF (best Unicode)
DOKUFY_PDF_RENDERER=mpdf
```

## PDF Options

### DOKUFY_PDF_FORMAT

Paper size for PDF output.

| Property | Value |
|----------|-------|
| Default | `A4` |
| Options | `A3`, `A4`, `A5`, `Letter`, `Legal`, `Tabloid` |

```bash
DOKUFY_PDF_FORMAT=A4
```

### DOKUFY_PDF_ORIENTATION

Page orientation.

| Property | Value |
|----------|-------|
| Default | `portrait` |
| Options | `portrait`, `landscape` |

```bash
DOKUFY_PDF_ORIENTATION=portrait
```

### DOKUFY_PDF_MARGIN_TOP

Top margin.

| Property | Value |
|----------|-------|
| Default | `1in` |
| Type | CSS unit (in, cm, mm, px) |

```bash
DOKUFY_PDF_MARGIN_TOP=1in
```

### DOKUFY_PDF_MARGIN_BOTTOM

Bottom margin.

| Property | Value |
|----------|-------|
| Default | `1in` |
| Type | CSS unit |

```bash
DOKUFY_PDF_MARGIN_BOTTOM=1in
```

### DOKUFY_PDF_MARGIN_LEFT

Left margin.

| Property | Value |
|----------|-------|
| Default | `0.5in` |
| Type | CSS unit |

```bash
DOKUFY_PDF_MARGIN_LEFT=0.5in
```

### DOKUFY_PDF_MARGIN_RIGHT

Right margin.

| Property | Value |
|----------|-------|
| Default | `0.5in` |
| Type | CSS unit |

```bash
DOKUFY_PDF_MARGIN_RIGHT=0.5in
```

## Templates

### DOKUFY_TEMPLATES_PATH

Default directory for template files.

| Property | Value |
|----------|-------|
| Default | `resource_path('templates')` |
| Type | Path |

```bash
# Default (resources/templates)
DOKUFY_TEMPLATES_PATH=

# Custom path
DOKUFY_TEMPLATES_PATH=/var/www/templates
```

## Environment-Specific Configurations

### Local Development

```bash
# .env.local
DOKUFY_DRIVER=fake
```

### Testing

```bash
# .env.testing
DOKUFY_DRIVER=fake
```

### Staging

```bash
# .env.staging
DOKUFY_DRIVER=gotenberg
DOKUFY_GOTENBERG_URL=http://gotenberg-staging:3000
```

### Production

```bash
# .env.production
DOKUFY_DRIVER=gotenberg
DOKUFY_GOTENBERG_URL=http://gotenberg.internal:3000
DOKUFY_GOTENBERG_TIMEOUT=300
```

## Next Steps

- [PDF Options](03-pdf-options.md) - Detailed PDF settings
- [Configuration File](01-config-file.md) - Full config reference
