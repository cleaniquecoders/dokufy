<?php

namespace CleaniqueCoders\Dokufy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\Dokufy\Dokufy
 */
class Dokufy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\Dokufy\Dokufy::class;
    }
}
