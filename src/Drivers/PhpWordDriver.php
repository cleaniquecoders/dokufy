<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;

class PhpWordDriver implements Driver
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    public function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = config('dokufy.drivers.phpword', []);
        $this->config = $config;
    }

    public function getName(): string
    {
        return 'phpword';
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
        $this->ensurePdfRendererAvailable();

        try {
            $phpWord = new \PhpOffice\PhpWord\PhpWord;

            // Add a section and HTML content
            $section = $phpWord->addSection();
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html);

            // Configure PDF renderer
            $this->configurePdfRenderer();

            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save as PDF
            $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
            $pdfWriter->save($outputPath);

            return $outputPath;
        } catch (\Throwable $e) {
            throw ConversionException::failed($e->getMessage());
        }
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        $this->ensureAvailable();
        $this->ensurePdfRendererAvailable();

        if (! file_exists($docxPath)) {
            throw ConversionException::failed("Source file not found: {$docxPath}");
        }

        try {
            // Load the DOCX file
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($docxPath);

            // Configure PDF renderer
            $this->configurePdfRenderer();

            $directory = dirname($outputPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save as PDF
            $pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
            $pdfWriter->save($outputPath);

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
        return ['docx'];
    }

    public function isAvailable(): bool
    {
        return class_exists(\PhpOffice\PhpWord\PhpWord::class);
    }

    protected function getPdfRenderer(): string
    {
        return $this->config['pdf_renderer'] ?? 'dompdf';
    }

    /**
     * @throws DriverException
     */
    protected function ensureAvailable(): void
    {
        if (! class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            throw DriverException::notConfigured('phpword');
        }
    }

    /**
     * @throws DriverException
     */
    protected function ensurePdfRendererAvailable(): void
    {
        $renderer = $this->getPdfRenderer();

        $rendererAvailable = match ($renderer) {
            'dompdf' => class_exists(\Dompdf\Dompdf::class),
            'tcpdf' => class_exists(\TCPDF::class),
            'mpdf' => class_exists(\Mpdf\Mpdf::class),
            default => false,
        };

        if (! $rendererAvailable) {
            throw DriverException::notConfigured("phpword (missing PDF renderer: {$renderer})");
        }
    }

    protected function configurePdfRenderer(): void
    {
        $renderer = $this->getPdfRenderer();

        $rendererName = match ($renderer) {
            'dompdf' => \PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF,
            'tcpdf' => \PhpOffice\PhpWord\Settings::PDF_RENDERER_TCPDF,
            'mpdf' => \PhpOffice\PhpWord\Settings::PDF_RENDERER_MPDF,
            default => \PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF,
        };

        $rendererPath = match ($renderer) {
            'dompdf' => $this->getVendorPath('dompdf/dompdf'),
            'tcpdf' => $this->getVendorPath('tecnickcom/tcpdf'),
            'mpdf' => $this->getVendorPath('mpdf/mpdf'),
            default => $this->getVendorPath('dompdf/dompdf'),
        };

        \PhpOffice\PhpWord\Settings::setPdfRendererName($rendererName);
        \PhpOffice\PhpWord\Settings::setPdfRendererPath($rendererPath);
    }

    protected function getVendorPath(string $package): string
    {
        // Try common vendor paths
        $basePaths = [
            base_path('vendor'),
            dirname(__DIR__, 4).'/vendor',
            dirname(__DIR__, 5).'/vendor',
        ];

        foreach ($basePaths as $basePath) {
            $path = $basePath.'/'.$package;
            if (is_dir($path)) {
                return $path;
            }
        }

        return base_path('vendor/'.$package);
    }
}
