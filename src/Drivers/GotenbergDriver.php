<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

class GotenbergDriver implements Driver
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = config('dokufy.drivers.gotenberg', []);
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'gotenberg';
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

        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            throw DriverException::notConfigured('gotenberg');
        }

        try {
            $request = \Gotenberg\Gotenberg::chromium($this->getUrl())
                ->pdf()
                ->html(\Gotenberg\Stream::string('index.html', $html));

            $response = \Gotenberg\Gotenberg::send($request);

            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($outputPath, $response->getBody()->getContents());

            return $outputPath;
        } catch (\Throwable $e) {
            throw ConversionException::failed($e->getMessage());
        }
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        $this->ensureAvailable();

        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            throw DriverException::notConfigured('gotenberg');
        }

        if (! file_exists($docxPath)) {
            throw ConversionException::failed("Source file not found: {$docxPath}");
        }

        try {
            $request = \Gotenberg\Gotenberg::libreOffice($this->getUrl())
                ->convert(\Gotenberg\Stream::path($docxPath));

            $response = \Gotenberg\Gotenberg::send($request);

            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($outputPath, $response->getBody()->getContents());

            return $outputPath;
        } catch (\Throwable $e) {
            throw ConversionException::failed($e->getMessage());
        }
    }

    /**
     * @return array<string>
     */
    public function supports(): array
    {
        return ['html', 'docx', 'xlsx', 'pptx', 'odt', 'markdown'];
    }

    public function isAvailable(): bool
    {
        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            return false;
        }

        $url = $this->getUrl();
        if (empty($url)) {
            return false;
        }

        // Try to ping the Gotenberg service
        try {
            $ch = curl_init($url.'/health');
            if ($ch === false) {
                return false;
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $httpCode === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function getUrl(): string
    {
        return $this->config['url'] ?? 'http://gotenberg:3000';
    }

    protected function getTimeout(): int
    {
        return (int) ($this->config['timeout'] ?? 120);
    }

    /**
     * @throws DriverException
     */
    protected function ensureAvailable(): void
    {
        if (! class_exists(\Gotenberg\Gotenberg::class)) {
            throw DriverException::notConfigured('gotenberg');
        }
    }
}
