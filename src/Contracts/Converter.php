<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Contracts;

interface Converter
{
    /**
     * Convert HTML content to PDF.
     */
    public function htmlToPdf(string $html, string $outputPath): string;

    /**
     * Convert DOCX file to PDF.
     */
    public function docxToPdf(string $docxPath, string $outputPath): string;

    /**
     * Get supported input formats.
     *
     * @return array<string>
     */
    public function supports(): array;

    /**
     * Check if driver is available/configured.
     */
    public function isAvailable(): bool;
}
