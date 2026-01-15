<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Drivers;

use CleaniqueCoders\Dokufy\Contracts\Driver;
use PHPUnit\Framework\Assert;

class FakeDriver implements Driver
{
    /**
     * @var array<int, array{method: string, args: array<mixed>}>
     */
    protected array $calls = [];

    /**
     * @var array<string>
     */
    protected array $generatedFiles = [];

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    public function __construct()
    {
        $this->config = config('dokufy.drivers.fake', []);
    }

    public function getName(): string
    {
        return 'fake';
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
        $this->recordCall('htmlToPdf', [$html, $outputPath]);
        $this->generatedFiles[] = $outputPath;

        return $outputPath;
    }

    public function docxToPdf(string $docxPath, string $outputPath): string
    {
        $this->recordCall('docxToPdf', [$docxPath, $outputPath]);
        $this->generatedFiles[] = $outputPath;

        return $outputPath;
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
        return true;
    }

    /**
     * Record a method call.
     *
     * @param  array<mixed>  $args
     */
    protected function recordCall(string $method, array $args): void
    {
        $this->calls[] = [
            'method' => $method,
            'args' => $args,
        ];
    }

    /**
     * Get all recorded method calls.
     *
     * @return array<int, array{method: string, args: array<mixed>}>
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * Get all generated file paths.
     *
     * @return array<string>
     */
    public function getGeneratedFiles(): array
    {
        return $this->generatedFiles;
    }

    /**
     * Assert that a file was generated at the given path.
     */
    public function assertGenerated(string $path): void
    {
        Assert::assertContains(
            $path,
            $this->generatedFiles,
            "Expected file to be generated at [{$path}], but it was not."
        );
    }

    /**
     * Assert that a PDF was generated.
     */
    public function assertPdfGenerated(): void
    {
        $pdfFiles = array_filter($this->generatedFiles, fn ($file) => str_ends_with($file, '.pdf'));

        Assert::assertNotEmpty(
            $pdfFiles,
            'Expected a PDF file to be generated, but none were.'
        );
    }

    /**
     * Assert that a DOCX was generated.
     */
    public function assertDocxGenerated(): void
    {
        $docxFiles = array_filter($this->generatedFiles, fn ($file) => str_ends_with($file, '.docx'));

        Assert::assertNotEmpty(
            $docxFiles,
            'Expected a DOCX file to be generated, but none were.'
        );
    }

    /**
     * Assert that a specific method was called.
     *
     * @param  array<mixed>|null  $withArgs
     */
    public function assertMethodCalled(string $method, ?array $withArgs = null): void
    {
        $called = array_filter($this->calls, fn ($call) => $call['method'] === $method);

        Assert::assertNotEmpty(
            $called,
            "Expected method [{$method}] to be called, but it was not."
        );

        if ($withArgs !== null) {
            $matchingCall = array_filter($called, fn ($call) => $call['args'] === $withArgs);

            Assert::assertNotEmpty(
                $matchingCall,
                "Expected method [{$method}] to be called with specific arguments, but no matching call was found."
            );
        }
    }

    /**
     * Assert that no files were generated.
     */
    public function assertNothingGenerated(): void
    {
        Assert::assertEmpty(
            $this->generatedFiles,
            'Expected no files to be generated, but some were.'
        );
    }

    /**
     * Reset the recorded calls and generated files.
     */
    public function reset(): void
    {
        $this->calls = [];
        $this->generatedFiles = [];
    }
}
