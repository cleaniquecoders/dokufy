# Exceptions

Dokufy provides specific exception classes for different error scenarios. All exceptions extend the base `DokufyException`.

## Exception Hierarchy

```text
DokufyException
├── DriverException
├── ConversionException
└── TemplateNotFoundException
```

## DokufyException

Base exception class for all Dokufy-related errors.

### Namespace

```php
use CleaniqueCoders\Dokufy\Exceptions\DokufyException;
```

### Catching All Dokufy Errors

```php
use CleaniqueCoders\Dokufy\Exceptions\DokufyException;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

try {
    Dokufy::template($template)
        ->data($data)
        ->toPdf($output);
} catch (DokufyException $e) {
    Log::error('Document generation failed', [
        'message' => $e->getMessage(),
        'template' => $template,
    ]);

    return response()->json(['error' => 'Document generation failed'], 500);
}
```

## DriverException

Thrown when a driver is unavailable, misconfigured, or cannot be resolved.

### Namespace

```php
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
```

### Common Scenarios

| Scenario | Message |
|----------|---------|
| Driver not found | `Driver 'custom' is not registered` |
| Driver unavailable | `Driver 'gotenberg' is not available` |
| Missing dependency | `Driver 'chromium' requires browsershot package` |
| Configuration error | `Driver 'gotenberg' is missing required URL` |

### Example

```php
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

try {
    Dokufy::driver('gotenberg')
        ->html($html)
        ->toPdf($output);
} catch (DriverException $e) {
    // Handle driver-specific errors
    if (str_contains($e->getMessage(), 'not available')) {
        // Fallback to another driver
        return Dokufy::driver('libreoffice')
            ->html($html)
            ->toPdf($output);
    }

    throw $e;
}
```

### Static Factory Methods

```php
// Driver not registered
throw DriverException::notFound('custom');

// Driver not available (e.g., Gotenberg not running)
throw DriverException::notAvailable('gotenberg');

// Missing required package
throw DriverException::missingDependency('chromium', 'spatie/browsershot');
```

## ConversionException

Thrown when the document conversion process fails.

### Namespace

```php
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
```

### Common Scenarios

| Scenario | Message |
|----------|---------|
| API error | `Gotenberg returned status 500` |
| Process failed | `LibreOffice process exited with code 1` |
| Timeout | `Conversion timed out after 120 seconds` |
| Invalid input | `Invalid HTML content provided` |
| Output error | `Cannot write to output path` |

### Example

```php
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

try {
    Dokufy::template($template)
        ->data($data)
        ->toPdf($output);
} catch (ConversionException $e) {
    Log::error('PDF conversion failed', [
        'error' => $e->getMessage(),
        'template' => $template,
        'output' => $output,
    ]);

    // Notify admin of conversion issues
    Notification::route('slack', config('logging.slack.webhook'))
        ->notify(new ConversionFailedNotification($e));

    throw $e;
}
```

### Static Factory Methods

```php
// Generic conversion failure
throw ConversionException::failed('Unexpected error during conversion');

// API error with status code
throw ConversionException::apiError('gotenberg', 500, 'Internal server error');

// Timeout
throw ConversionException::timeout(120);

// Cannot write output
throw ConversionException::cannotWriteOutput($outputPath);
```

## TemplateNotFoundException

Thrown when a template file cannot be found.

### Namespace

```php
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;
```

### Example

```php
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

try {
    Dokufy::template($templatePath)
        ->data($data)
        ->toPdf($output);
} catch (TemplateNotFoundException $e) {
    Log::warning('Template not found', ['path' => $templatePath]);

    // Use a default template
    return Dokufy::template(resource_path('templates/default.docx'))
        ->data($data)
        ->toPdf($output);
}
```

### Static Factory Methods

```php
// Template file not found
throw TemplateNotFoundException::atPath('/path/to/missing.docx');
```

## Best Practices

### Specific Exception Handling

Handle exceptions from most specific to least specific:

```php
use CleaniqueCoders\Dokufy\Exceptions\{
    DokufyException,
    DriverException,
    ConversionException,
    TemplateNotFoundException
};

try {
    Dokufy::template($template)
        ->data($data)
        ->toPdf($output);
} catch (TemplateNotFoundException $e) {
    // Handle missing template
    return $this->useDefaultTemplate($data, $output);
} catch (DriverException $e) {
    // Handle driver issues - try fallback
    return $this->fallbackDriver($template, $data, $output);
} catch (ConversionException $e) {
    // Handle conversion failure
    return $this->handleConversionError($e);
} catch (DokufyException $e) {
    // Handle any other Dokufy error
    return $this->handleGenericError($e);
}
```

### Retry Logic

Implement retry for transient failures:

```php
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;

function generateWithRetry(string $template, array $data, string $output, int $maxAttempts = 3): string
{
    $attempts = 0;
    $lastException = null;

    while ($attempts < $maxAttempts) {
        try {
            return Dokufy::template($template)
                ->data($data)
                ->toPdf($output);
        } catch (ConversionException $e) {
            $lastException = $e;
            $attempts++;

            if ($attempts < $maxAttempts) {
                sleep(pow(2, $attempts)); // Exponential backoff
            }
        }
    }

    throw $lastException;
}
```

### User-Friendly Error Messages

Convert exceptions to user-friendly messages:

```php
use CleaniqueCoders\Dokufy\Exceptions\{
    DokufyException,
    DriverException,
    ConversionException,
    TemplateNotFoundException
};

function getUserMessage(DokufyException $e): string
{
    return match (true) {
        $e instanceof TemplateNotFoundException => 'The document template could not be found.',
        $e instanceof DriverException => 'The document service is temporarily unavailable.',
        $e instanceof ConversionException => 'The document could not be generated. Please try again.',
        default => 'An unexpected error occurred while generating the document.',
    };
}
```

## Next Steps

- [Examples](../06-examples/README.md) - Error handling in practice
- [Testing](../07-testing/README.md) - Testing error scenarios
