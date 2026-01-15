<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use Illuminate\Support\Facades\Process;

class LibreOfficeDriver implements Driver
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = config('dokufy.drivers.libreoffice', []);
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'libreoffice';
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
        // Create a temporary HTML file
        $tempHtmlPath = sys_get_temp_dir().'/'.uniqid('dokufy_html_', true).'.html';
        file_put_contents($tempHtmlPath, $html);

        try {
            $this->convertToPdf($tempHtmlPath, dirname($outputPath));

            // LibreOffice outputs to the same directory with .pdf extension
            $generatedPdf = dirname($outputPath).'/'.pathinfo($tempHtmlPath, PATHINFO_FILENAME).'.pdf';

            if (file_exists($generatedPdf)) {
                rename($generatedPdf, $outputPath);
            }

            return $outputPath;
        } finally {
            @unlink($tempHtmlPath);
        }
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        if (! file_exists($docxPath)) {
            throw ConversionException::failed("Source file not found: {$docxPath}");
        }

        $outputDir = dirname($outputPath);

        $this->convertToPdf($docxPath, $outputDir);

        // LibreOffice outputs to the same directory with .pdf extension
        $generatedPdf = $outputDir.'/'.pathinfo($docxPath, PATHINFO_FILENAME).'.pdf';

        if (file_exists($generatedPdf) && $generatedPdf !== $outputPath) {
            rename($generatedPdf, $outputPath);
        }

        if (! file_exists($outputPath)) {
            throw ConversionException::outputFailed($outputPath);
        }

        return $outputPath;
    }

    /**
     * @return array<string>
     */
    public function supports(): array
    {
        return ['html', 'docx', 'xlsx', 'pptx', 'odt'];
    }

    public function isAvailable(): bool
    {
        $binary = $this->getBinary();

        // Check if the binary exists and is executable
        $result = Process::run("which {$binary}");

        if (! $result->successful()) {
            // Try common paths on macOS
            $commonPaths = [
                '/Applications/LibreOffice.app/Contents/MacOS/soffice',
                '/usr/bin/libreoffice',
                '/usr/local/bin/libreoffice',
            ];

            foreach ($commonPaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    protected function getBinary(): string
    {
        return $this->config['binary'] ?? 'libreoffice';
    }

    protected function getTimeout(): int
    {
        return (int) ($this->config['timeout'] ?? 120);
    }

    protected function convertToPdf(string $inputPath, string $outputDir): void
    {
        $binary = $this->resolveBinaryPath();

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $command = sprintf(
            '%s --headless --convert-to pdf --outdir %s %s',
            escapeshellarg($binary),
            escapeshellarg($outputDir),
            escapeshellarg($inputPath)
        );

        $result = Process::timeout($this->getTimeout())->run($command);

        if (! $result->successful()) {
            throw ConversionException::failed($result->errorOutput() ?: 'LibreOffice conversion failed');
        }
    }

    protected function resolveBinaryPath(): string
    {
        $binary = $this->getBinary();

        // Check if the configured binary is a full path
        if (file_exists($binary) && is_executable($binary)) {
            return $binary;
        }

        // Check common paths on macOS
        $commonPaths = [
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            '/usr/bin/libreoffice',
            '/usr/local/bin/libreoffice',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return $binary;
    }
}
