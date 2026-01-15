<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Exceptions;

class DriverException extends DokufyException
{
    public static function notFound(string $driver): self
    {
        return new self("Driver [{$driver}] not found.");
    }

    public static function notAvailable(string $driver): self
    {
        return new self("Driver [{$driver}] is not available. Please check your configuration.");
    }

    public static function notConfigured(string $driver): self
    {
        return new self("Driver [{$driver}] is not properly configured.");
    }
}
