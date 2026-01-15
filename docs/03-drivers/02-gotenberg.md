# Gotenberg Driver

The Gotenberg driver uses the [Gotenberg](https://gotenberg.dev) API for document
conversion. It's the recommended driver for production environments using Docker.

## Requirements

- Docker
- Gotenberg container (v8+)
- `gotenberg/gotenberg-php` package

## Installation

Install the PHP client:

```bash
composer require gotenberg/gotenberg-php
```

Start the Gotenberg container:

```bash
# Using Docker
docker run -d -p 3000:3000 gotenberg/gotenberg:8

# Using Docker Compose
docker compose up -d gotenberg
```

### Docker Compose Configuration

```yaml
# docker-compose.yml
services:
  gotenberg:
    image: gotenberg/gotenberg:8
    ports:
      - "3000:3000"
    environment:
      - CHROMIUM_DISABLE_JAVASCRIPT=false
      - CHROMIUM_ALLOW_LIST=file:///tmp/.*
    restart: unless-stopped
```

## Configuration

```php
// config/dokufy.php
'drivers' => [
    'gotenberg' => [
        'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
        'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
    ],
],
```

### Environment Variables

```bash
# .env
DOKUFY_DRIVER=gotenberg
DOKUFY_GOTENBERG_URL=http://localhost:3000
DOKUFY_GOTENBERG_TIMEOUT=120
```

## Supported Formats

| Input | Output |
|-------|--------|
| HTML | PDF |
| DOCX | PDF |
| XLSX | PDF |
| PPTX | PDF |
| ODT | PDF |
| ODS | PDF |
| ODP | PDF |
| Markdown | PDF |

## Usage Examples

### HTML to PDF

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

Dokufy::driver('gotenberg')
    ->html('<h1>Hello World</h1>')
    ->toPdf(storage_path('documents/hello.pdf'));
```

### DOCX to PDF

```php
Dokufy::driver('gotenberg')
    ->template(resource_path('templates/contract.docx'))
    ->data(['party_a' => 'Company A', 'party_b' => 'Company B'])
    ->toPdf(storage_path('contracts/contract.pdf'));
```

### Blade View to PDF

```php
$html = view('invoices.template', [
    'invoice' => $invoice,
    'items' => $items,
])->render();

Dokufy::driver('gotenberg')
    ->html($html)
    ->toPdf(storage_path("invoices/{$invoice->number}.pdf"));
```

## Advanced Features

### Custom Margins

Gotenberg supports custom page margins:

```php
// Set via PDF config
// config/dokufy.php
'pdf' => [
    'margin_top' => '0.5in',
    'margin_bottom' => '0.5in',
    'margin_left' => '0.5in',
    'margin_right' => '0.5in',
],
```

### Landscape Orientation

```php
// config/dokufy.php
'pdf' => [
    'orientation' => 'landscape',
],
```

### Custom Paper Size

```php
// config/dokufy.php
'pdf' => [
    'format' => 'A3', // A3, A4, A5, Letter, Legal, Tabloid
],
```

## Kubernetes Deployment

```yaml
# gotenberg-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: gotenberg
spec:
  replicas: 2
  selector:
    matchLabels:
      app: gotenberg
  template:
    metadata:
      labels:
        app: gotenberg
    spec:
      containers:
        - name: gotenberg
          image: gotenberg/gotenberg:8
          ports:
            - containerPort: 3000
          resources:
            requests:
              memory: "512Mi"
              cpu: "250m"
            limits:
              memory: "1Gi"
              cpu: "500m"
---
apiVersion: v1
kind: Service
metadata:
  name: gotenberg
spec:
  selector:
    app: gotenberg
  ports:
    - port: 3000
      targetPort: 3000
```

## Troubleshooting

### Connection Refused

```text
Error: Connection refused to http://localhost:3000
```

**Solution**: Ensure Gotenberg container is running:

```bash
docker ps | grep gotenberg
```

### Timeout Errors

```text
Error: Request timeout after 120 seconds
```

**Solution**: Increase timeout for complex documents:

```bash
DOKUFY_GOTENBERG_TIMEOUT=300
```

### Memory Issues

For large documents, increase container memory:

```yaml
services:
  gotenberg:
    image: gotenberg/gotenberg:8
    deploy:
      resources:
        limits:
          memory: 2G
```

## Checking Availability

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

if (Dokufy::isDriverAvailable('gotenberg')) {
    // Gotenberg is ready
}
```

## Next Steps

- [LibreOffice Driver](03-libreoffice.md) - Alternative for non-Docker environments
- [Configuration](../05-configuration/README.md) - All configuration options
