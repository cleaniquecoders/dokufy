<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Contracts;

interface Driver extends Converter
{
    /**
     * Get the driver name.
     */
    public function getName(): string;

    /**
     * Get driver-specific configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}
