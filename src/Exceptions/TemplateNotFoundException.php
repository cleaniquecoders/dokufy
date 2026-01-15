<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Exceptions;

class TemplateNotFoundException extends DokufyException
{
    public static function atPath(string $path): self
    {
        return new self("Template not found at: {$path}");
    }
}
