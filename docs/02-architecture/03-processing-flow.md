# Processing Flow

This document explains how Dokufy processes a document generation request from input to output.

## Overview

The processing flow consists of four main stages:

1. **Input**: Accept template or HTML content
2. **Data Binding**: Replace placeholders with values
3. **Conversion**: Convert to target format via driver
4. **Output**: Save to file or return HTTP response

## Flow Diagram

```text
┌─────────────────────────────────────────────────────────────────────┐
│                           User Code                                  │
│  Dokufy::template($path)->data($array)->toPdf($output)              │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         1. INPUT STAGE                               │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  template($path)           │  html($content)                │    │
│  │  - Validate file exists    │  - Store HTML content          │    │
│  │  - Store template path     │  - Set template type to html   │    │
│  │  - Detect format (docx/html)                                │    │
│  └─────────────────────────────────────────────────────────────┘    │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      2. DATA BINDING STAGE                           │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  data($array)              │  with($handler)                │    │
│  │  - Store key-value pairs   │  - Store PlaceholderHandler    │    │
│  │                            │  - Will call toArray() later   │    │
│  └─────────────────────────────────────────────────────────────┘    │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      3. CONVERSION STAGE                             │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  toPdf($outputPath)                                         │    │
│  │  1. Resolve data (array or handler->toArray())              │    │
│  │  2. Process placeholders in content                         │    │
│  │  3. Create output directory if needed                       │    │
│  │  4. Call driver's htmlToPdf() or docxToPdf()               │    │
│  │  5. Return output path                                      │    │
│  └─────────────────────────────────────────────────────────────┘    │
└────────────────────────────────┬────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        4. OUTPUT STAGE                               │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  File Output               │  HTTP Response                 │    │
│  │  - toPdf(): Save to path   │  - stream(): StreamedResponse  │    │
│  │  - toDocx(): Save to path  │  - download(): BinaryFileResp  │    │
│  │  - Returns file path       │  - Auto-cleanup temp files     │    │
│  └─────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
```

## Stage 1: Input

### Template Input

When you call `template($path)`:

```php
public function template(string $path): self
{
    if (!file_exists($path)) {
        throw TemplateNotFoundException::atPath($path);
    }

    $this->templatePath = $path;
    $this->templateType = pathinfo($path, PATHINFO_EXTENSION);

    return $this;
}
```

### HTML Input

When you call `html($content)`:

```php
public function html(string $content): self
{
    $this->htmlContent = $content;
    $this->templateType = 'html';

    return $this;
}
```

## Stage 2: Data Binding

### Array Data

```php
public function data(array $data): self
{
    $this->data = $data;
    return $this;
}
```

### Placeholdify Handler

Using the `InteractsWithPlaceholdify` trait:

```php
public function with(object $handler): self
{
    $this->placeholderHandler = $handler;
    return $this;
}
```

## Stage 3: Conversion

The `toPdf()` method orchestrates the conversion:

```php
public function toPdf(string $outputPath): string
{
    // 1. Resolve data
    $data = $this->resolveData();

    // 2. Process content
    $content = $this->processContent($data);

    // 3. Ensure output directory exists
    $this->ensureDirectoryExists(dirname($outputPath));

    // 4. Delegate to driver
    if ($this->templateType === 'html') {
        return $this->getDriver()->htmlToPdf($content, $outputPath);
    }

    return $this->getDriver()->docxToPdf($content, $outputPath);
}
```

### Placeholder Processing

Placeholders are replaced using regex:

```php
protected function processPlaceholders(string $content, array $data): string
{
    foreach ($data as $key => $value) {
        // Handle {{ key }}, {{key}}, {{ key}}, {{key }}
        $pattern = '/{{\s*' . preg_quote($key, '/') . '\s*}}/';
        $replacement = $this->formatValue($value);
        $content = preg_replace($pattern, $replacement, $content);
    }

    return $content;
}
```

## Stage 4: Output

### File Output

```php
// toPdf() and toDocx() return the output path
$path = Dokufy::template($t)->data($d)->toPdf($output);
// $path === $output
```

### HTTP Response

For `stream()` and `download()`:

```php
public function stream(?string $filename = null): StreamedResponse
{
    $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
    $this->toPdf($tempPath);

    return response()->streamDownload(function () use ($tempPath) {
        $handle = fopen($tempPath, 'rb');
        fpassthru($handle);
        fclose($handle);
        unlink($tempPath);  // Cleanup
    }, $filename ?? 'document.pdf', [
        'Content-Type' => 'application/pdf',
    ]);
}
```

## Driver-Specific Processing

Each driver handles the actual conversion differently:

### Gotenberg

1. Create HTTP client
2. Build multipart form request
3. POST to Gotenberg API
4. Write response body to output file

### LibreOffice

1. Write content to temp file if needed
2. Execute: `libreoffice --headless --convert-to pdf --outdir $dir $input`
3. Move/rename output file

### Chromium

1. Create Browsershot instance
2. Set HTML content
3. Configure margins and options
4. Call `save($outputPath)`

### PHPWord

1. Load DOCX with PhpWord
2. Set PDF renderer path
3. Write to PDF via renderer

## Error Handling

Errors at each stage throw specific exceptions:

| Stage | Exception | Cause |
|-------|-----------|-------|
| Input | `TemplateNotFoundException` | Template file doesn't exist |
| Conversion | `ConversionException` | Driver conversion failed |
| Conversion | `DriverException` | Driver not available |
| Output | `ConversionException` | Cannot write output file |

## Next Steps

- [Drivers](../03-drivers/README.md) - Driver-specific details
- [API Reference](../04-api/README.md) - Complete method reference
