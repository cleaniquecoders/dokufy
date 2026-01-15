<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Exceptions;

class ConversionException extends DokufyException
{
    public static function failed(string $message): self
    {
        return new self("Document conversion failed: {$message}");
    }

    public static function unsupportedFormat(string $format): self
    {
        return new self("Unsupported format: {$format}");
    }

    public static function outputFailed(string $path): self
    {
        return new self("Failed to write output to: {$path}");
    }
}
