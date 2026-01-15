<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy;

use CleaniqueCoders\Dokufy\Concerns\InteractsWithPlaceholdify;
use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Dokufy
{
    use InteractsWithPlaceholdify;

    protected ?string $templatePath = null;

    protected ?string $htmlContent = null;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    protected ?Driver $driver = null;

    protected ?FakeDriver $fakeDriver = null;

    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set the template path.
     *
     * @throws TemplateNotFoundException
     */
    public function template(string $path): self
    {
        if (! File::exists($path)) {
            throw TemplateNotFoundException::atPath($path);
        }

        $this->templatePath = $path;
        $this->htmlContent = null;

        return $this;
    }

    /**
     * Set HTML content directly.
     */
    public function html(string $content): self
    {
        $this->htmlContent = $content;
        $this->templatePath = null;

        return $this;
    }

    /**
     * Set data for placeholder replacement.
     *
     * @param  array<string, mixed>  $data
     */
    public function data(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Convert to PDF and save to the given path.
     */
    public function toPdf(string $outputPath): string
    {
        $driver = $this->resolveDriver();

        if ($this->htmlContent !== null) {
            $html = $this->processPlaceholders($this->htmlContent, $this->data);

            return $driver->htmlToPdf($html, $outputPath);
        }

        if ($this->templatePath !== null) {
            $extension = strtolower(pathinfo($this->templatePath, PATHINFO_EXTENSION));

            if ($extension === 'html' || $extension === 'htm') {
                $html = File::get($this->templatePath);
                $html = $this->processPlaceholders($html, $this->data);

                return $driver->htmlToPdf($html, $outputPath);
            }

            return $driver->docxToPdf($this->templatePath, $outputPath);
        }

        throw new \RuntimeException('No template or HTML content has been set.');
    }

    /**
     * Convert to DOCX and save to the given path.
     */
    public function toDocx(string $outputPath): string
    {
        if ($this->templatePath === null) {
            throw new \RuntimeException('A template is required for DOCX output.');
        }

        // For DOCX output, we copy and process the template
        File::copy($this->templatePath, $outputPath);

        return $outputPath;
    }

    /**
     * Stream the PDF directly to the browser.
     */
    public function stream(?string $filename = null): StreamedResponse
    {
        $filename = $filename ?? 'document.pdf';
        $tempPath = sys_get_temp_dir().'/'.uniqid('dokufy_', true).'.pdf';

        $this->toPdf($tempPath);

        return new StreamedResponse(function () use ($tempPath) {
            $stream = fopen($tempPath, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
            @unlink($tempPath);
        }, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * Download the PDF.
     */
    public function download(?string $filename = null): BinaryFileResponse
    {
        $filename = $filename ?? 'document.pdf';
        $tempPath = sys_get_temp_dir().'/'.uniqid('dokufy_', true).'.pdf';

        $this->toPdf($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Set the driver to use.
     *
     * @throws DriverException
     */
    public function driver(string $name): self
    {
        $this->driver = $this->resolveDriverByName($name);

        return $this;
    }

    /**
     * Create a new instance with the specified driver.
     *
     * @throws DriverException
     */
    public function make(?string $driver = null): self
    {
        $instance = new self($this->container);

        if ($driver !== null) {
            $instance->driver($driver);
        }

        return $instance;
    }

    /**
     * Get all available driver names.
     *
     * @return array<int, string>
     */
    public function getAvailableDrivers(): array
    {
        $drivers = [];

        /** @var array<string, mixed> $driversConfig */
        $driversConfig = config('dokufy.drivers', []);
        $configuredDrivers = array_keys($driversConfig);

        foreach ($configuredDrivers as $name) {
            $driverName = (string) $name;

            try {
                $driver = $this->resolveDriverByName($driverName);
                if ($driver->isAvailable()) {
                    $drivers[] = $driverName;
                }
            } catch (\Throwable) {
                // Driver not available, skip
            }
        }

        return $drivers;
    }

    /**
     * Check if a specific driver is available.
     */
    public function isDriverAvailable(string $driver): bool
    {
        try {
            $resolvedDriver = $this->resolveDriverByName($driver);

            return $resolvedDriver->isAvailable();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Use a fake driver for testing.
     */
    public function fake(): FakeDriver
    {
        $this->fakeDriver = new FakeDriver;
        $this->driver = $this->fakeDriver;

        return $this->fakeDriver;
    }

    /**
     * Assert that a file was generated at the given path.
     */
    public function assertGenerated(string $path): void
    {
        $this->getFakeDriver()->assertGenerated($path);
    }

    /**
     * Assert that a PDF was generated.
     */
    public function assertPdfGenerated(): void
    {
        $this->getFakeDriver()->assertPdfGenerated();
    }

    /**
     * Assert that a DOCX was generated.
     */
    public function assertDocxGenerated(): void
    {
        $this->getFakeDriver()->assertDocxGenerated();
    }

    /**
     * Resolve the driver to use.
     */
    protected function resolveDriver(): Driver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        /** @var string $defaultDriver */
        $defaultDriver = config('dokufy.default', 'gotenberg');

        return $this->resolveDriverByName($defaultDriver);
    }

    /**
     * Resolve a driver by name.
     *
     * @throws DriverException
     */
    protected function resolveDriverByName(string $name): Driver
    {
        $abstract = "dokufy.driver.{$name}";

        if (! $this->container->bound($abstract)) {
            throw DriverException::notFound($name);
        }

        $driver = $this->container->make($abstract);

        if (! $driver instanceof Driver) {
            throw DriverException::notConfigured($name);
        }

        return $driver;
    }

    /**
     * Get the fake driver instance.
     *
     * @throws \RuntimeException
     */
    protected function getFakeDriver(): FakeDriver
    {
        if ($this->fakeDriver === null) {
            throw new \RuntimeException('No fake driver has been set. Call fake() first.');
        }

        return $this->fakeDriver;
    }

    /**
     * Reset the instance state.
     */
    public function reset(): self
    {
        $this->templatePath = null;
        $this->htmlContent = null;
        $this->data = [];
        $this->driver = null;
        $this->placeholderHandler = null;

        return $this;
    }
}
