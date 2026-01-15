<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Commands;

use CleaniqueCoders\Dokufy\Dokufy;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class GenerateCommand extends Command
{
    /**
     * @var string
     */
    public $signature = 'dokufy:generate
        {input? : The input file path (HTML or DOCX template)}
        {output? : The output file path}
        {--driver= : The driver to use for conversion}
        {--data= : JSON string of placeholder data}
        {--data-file= : Path to JSON file containing placeholder data}
        {--force : Overwrite output file if it exists}';

    /**
     * @var string
     */
    public $description = 'Generate a document (PDF or DOCX) from a template';

    public function handle(Dokufy $dokufy): int
    {
        $input = $this->argument('input') ?? $this->promptForInput();
        $output = $this->argument('output') ?? $this->promptForOutput($input);

        // Validate input file
        if (! File::exists($input)) {
            $this->components->error("Input file not found: {$input}");

            return self::FAILURE;
        }

        // Check if output exists
        if (File::exists($output) && ! $this->option('force')) {
            if (! confirm("Output file [{$output}] already exists. Overwrite?", false)) {
                $this->components->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        // Get data for placeholders
        $data = $this->getPlaceholderData();

        // Get driver
        $driverName = $this->option('driver');

        try {
            if ($driverName) {
                $dokufy->driver($driverName);
            }

            $this->components->info('Generating document...');

            $extension = strtolower(pathinfo($input, PATHINFO_EXTENSION));
            $outputExtension = strtolower(pathinfo($output, PATHINFO_EXTENSION));

            // Set template or HTML content based on input type
            if ($extension === 'html' || $extension === 'htm') {
                $htmlContent = File::get($input);
                $dokufy->html($htmlContent);
            } else {
                $dokufy->template($input);
            }

            // Apply data
            if (! empty($data)) {
                $dokufy->data($data);
            }

            // Generate output
            if ($outputExtension === 'pdf') {
                $dokufy->toPdf($output);
            } elseif ($outputExtension === 'docx') {
                $dokufy->toDocx($output);
            } else {
                $this->components->error("Unsupported output format: {$outputExtension}");

                return self::FAILURE;
            }

            $this->components->success("Document generated successfully: {$output}");
            $this->components->twoColumnDetail('Input', $input);
            $this->components->twoColumnDetail('Output', $output);

            if (File::exists($output)) {
                $this->components->twoColumnDetail('Size', $this->formatBytes(File::size($output)));
            }

            return self::SUCCESS;
        } catch (DriverException $e) {
            $this->components->error($e->getMessage());
            $this->components->info('Available drivers: '.implode(', ', $dokufy->getAvailableDrivers()));

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->components->error('Generation failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    protected function promptForInput(): string
    {
        return text(
            label: 'Enter the input file path',
            placeholder: 'e.g., resources/templates/invoice.html',
            required: true,
            validate: function (string $value): ?string {
                if (! File::exists($value)) {
                    return 'File does not exist.';
                }

                return null;
            }
        );
    }

    protected function promptForOutput(string $input): string
    {
        $baseName = pathinfo($input, PATHINFO_FILENAME);
        $defaultOutput = storage_path("app/{$baseName}.pdf");

        $format = select(
            label: 'Select output format',
            options: [
                'pdf' => 'PDF Document',
                'docx' => 'Word Document (DOCX)',
            ],
            default: 'pdf'
        );

        return text(
            label: 'Enter the output file path',
            placeholder: $defaultOutput,
            default: storage_path("app/{$baseName}.{$format}"),
            required: true
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getPlaceholderData(): array
    {
        // Try data option first
        $jsonData = $this->option('data');
        if ($jsonData) {
            $decoded = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->components->warn('Invalid JSON in --data option. Ignoring.');

                return [];
            }

            return $decoded;
        }

        // Try data-file option
        $dataFile = $this->option('data-file');
        if ($dataFile) {
            if (! File::exists($dataFile)) {
                $this->components->warn("Data file not found: {$dataFile}. Ignoring.");

                return [];
            }

            $content = File::get($dataFile);
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->components->warn('Invalid JSON in data file. Ignoring.');

                return [];
            }

            return $decoded;
        }

        return [];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[(int) $factor]);
    }
}
