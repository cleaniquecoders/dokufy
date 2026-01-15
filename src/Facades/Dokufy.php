<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \CleaniqueCoders\Dokufy\Dokufy template(string $path)
 * @method static \CleaniqueCoders\Dokufy\Dokufy html(string $content)
 * @method static \CleaniqueCoders\Dokufy\Dokufy data(array<string, mixed> $data)
 * @method static \CleaniqueCoders\Dokufy\Dokufy with(object $handler)
 * @method static string toPdf(string $outputPath)
 * @method static string toDocx(string $outputPath)
 * @method static \Symfony\Component\HttpFoundation\StreamedResponse stream(?string $filename = null)
 * @method static \Symfony\Component\HttpFoundation\BinaryFileResponse download(?string $filename = null)
 * @method static \CleaniqueCoders\Dokufy\Dokufy driver(string $name)
 * @method static \CleaniqueCoders\Dokufy\Dokufy make(?string $driver = null)
 * @method static array<int, string> getAvailableDrivers()
 * @method static bool isDriverAvailable(string $driver)
 * @method static \CleaniqueCoders\Dokufy\Drivers\FakeDriver fake()
 * @method static void assertGenerated(string $path)
 * @method static void assertPdfGenerated()
 * @method static void assertDocxGenerated()
 * @method static \CleaniqueCoders\Dokufy\Dokufy reset()
 *
 * @see \CleaniqueCoders\Dokufy\Dokufy
 */
class Dokufy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\Dokufy\Dokufy::class;
    }
}
