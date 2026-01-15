<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

class ChromiumDriver implements Driver
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = config('dokufy.drivers.chromium', []);
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'chromium';
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function htmlToPdf(string $html, string $outputPath): string
    {
        $this->ensureAvailable();

        try {
            /** @var \Spatie\Browsershot\Browsershot $browsershot */
            $browsershot = \Spatie\Browsershot\Browsershot::html($html);

            $this->configureBrowsershot($browsershot);
            $this->applyPdfOptions($browsershot);

            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $browsershot->save($outputPath);

            return $outputPath;
        } catch (\Throwable $e) {
            throw ConversionException::failed($e->getMessage());
        }
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        // Chromium/Browsershot doesn't support DOCX directly
        throw ConversionException::unsupportedFormat('docx');
    }

    /**
     * @return array<string>
     */
    public function supports(): array
    {
        return ['html'];
    }

    public function isAvailable(): bool
    {
        return class_exists(\Spatie\Browsershot\Browsershot::class);
    }

    /**
     * @throws DriverException
     */
    protected function ensureAvailable(): void
    {
        if (! class_exists(\Spatie\Browsershot\Browsershot::class)) {
            throw DriverException::notConfigured('chromium');
        }
    }

    /**
     * Configure Browsershot with driver-specific settings.
     *
     * @param  \Spatie\Browsershot\Browsershot  $browsershot
     */
    protected function configureBrowsershot(object $browsershot): void
    {
        if (! empty($this->config['node_binary'])) {
            $browsershot->setNodeBinary($this->config['node_binary']);
        }

        if (! empty($this->config['npm_binary'])) {
            $browsershot->setNpmBinary($this->config['npm_binary']);
        }

        $timeout = (int) ($this->config['timeout'] ?? 60);
        $browsershot->timeout($timeout);
    }

    /**
     * Apply PDF options from configuration.
     *
     * @param  \Spatie\Browsershot\Browsershot  $browsershot
     */
    protected function applyPdfOptions(object $browsershot): void
    {
        /** @var array<string, mixed> $pdfConfig */
        $pdfConfig = config('dokufy.pdf', []);

        $format = $pdfConfig['format'] ?? 'A4';
        $orientation = $pdfConfig['orientation'] ?? 'portrait';

        $browsershot->format($format);

        if ($orientation === 'landscape') {
            $browsershot->landscape();
        }

        $marginTop = $pdfConfig['margin_top'] ?? '1in';
        $marginBottom = $pdfConfig['margin_bottom'] ?? '1in';
        $marginLeft = $pdfConfig['margin_left'] ?? '0.5in';
        $marginRight = $pdfConfig['margin_right'] ?? '0.5in';

        $browsershot->margins(
            $this->parseMargin($marginTop),
            $this->parseMargin($marginRight),
            $this->parseMargin($marginBottom),
            $this->parseMargin($marginLeft)
        );
    }

    /**
     * Parse margin value to millimeters.
     */
    protected function parseMargin(string $margin): float
    {
        $value = (float) preg_replace('/[^0-9.]/', '', $margin);
        $unit = (string) preg_replace('/[0-9.]/', '', $margin);

        return match (strtolower($unit)) {
            'in' => $value * 25.4,
            'cm' => $value * 10,
            'mm' => $value,
            default => $value,
        };
    }
}
