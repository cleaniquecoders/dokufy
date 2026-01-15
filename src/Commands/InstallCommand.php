<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Commands;

use CleaniqueCoders\Dokufy\Dokufy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    public $signature = 'dokufy:install
        {--force : Overwrite existing configuration}';

    /**
     * @var string
     */
    public $description = 'Install and configure Dokufy for your application';

    public function handle(Dokufy $dokufy): int
    {
        $this->components->info('Installing Dokufy...');
        $this->newLine();

        // Step 1: Publish configuration
        $this->publishConfiguration();

        // Step 2: Create templates directory
        $this->createTemplatesDirectory();

        // Step 3: Check and suggest driver installation
        $this->checkDrivers($dokufy);

        // Step 4: Show next steps
        $this->showNextSteps();

        return self::SUCCESS;
    }

    protected function publishConfiguration(): void
    {
        $configPath = config_path('dokufy.php');

        if (File::exists($configPath) && ! $this->option('force')) {
            if (confirm('Configuration file already exists. Overwrite?', false)) {
                $this->publishConfig();
            } else {
                $this->components->twoColumnDetail('Config', '<fg=yellow>Skipped (already exists)</>');
            }
        } else {
            $this->publishConfig();
        }
    }

    protected function publishConfig(): void
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'dokufy-config',
            '--force' => true,
        ]);

        $this->components->twoColumnDetail('Config', '<fg=green>Published</>');
    }

    protected function createTemplatesDirectory(): void
    {
        /** @var string $templatesPath */
        $templatesPath = config('dokufy.templates.path', resource_path('templates'));

        if (! File::isDirectory($templatesPath)) {
            File::makeDirectory($templatesPath, 0755, true);
            $this->components->twoColumnDetail('Templates Directory', '<fg=green>Created</>');

            // Create a sample template
            $this->createSampleTemplate($templatesPath);
        } else {
            $this->components->twoColumnDetail('Templates Directory', '<fg=yellow>Already exists</>');
        }
    }

    protected function createSampleTemplate(string $templatesPath): void
    {
        $sampleContent = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .content {
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>{{ title }}</h1>
    <div class="content">
        <p>Hello, <strong>{{ name }}</strong>!</p>
        <p>{{ content }}</p>
    </div>
    <div class="footer">
        <p>Generated on {{ date }} by Dokufy</p>
    </div>
</body>
</html>
HTML;

        File::put($templatesPath.'/sample.html', $sampleContent);
        $this->components->twoColumnDetail('Sample Template', '<fg=green>Created</>');
    }

    protected function checkDrivers(Dokufy $dokufy): void
    {
        $this->newLine();
        $this->components->info('Checking available drivers...');
        $this->newLine();

        $drivers = [
            'gotenberg' => [
                'available' => $dokufy->isDriverAvailable('gotenberg'),
                'package' => 'gotenberg/gotenberg-php',
                'description' => 'Docker-based PDF generation',
            ],
            'chromium' => [
                'available' => $dokufy->isDriverAvailable('chromium'),
                'package' => 'spatie/browsershot',
                'description' => 'Browser-based PDF generation',
            ],
            'phpword' => [
                'available' => $dokufy->isDriverAvailable('phpword'),
                'package' => 'phpoffice/phpword',
                'description' => 'Native PHP DOCX processing',
            ],
            'libreoffice' => [
                'available' => $dokufy->isDriverAvailable('libreoffice'),
                'package' => null,
                'description' => 'CLI-based conversion (requires LibreOffice)',
            ],
        ];

        $unavailable = [];

        foreach ($drivers as $name => $info) {
            $status = $info['available']
                ? '<fg=green>✓ Available</>'
                : '<fg=red>✗ Not Available</>';

            $this->components->twoColumnDetail(ucfirst($name), $status);

            if (! $info['available'] && $info['package']) {
                $unavailable[$name] = $info;
            }
        }

        if (! empty($unavailable) && confirm("\nWould you like to install additional drivers?", false)) {
            $this->installSelectedDrivers($unavailable);
        }
    }

    /**
     * @param  array<string, array{available: bool, package: string|null, description: string}>  $unavailable
     */
    protected function installSelectedDrivers(array $unavailable): void
    {
        $options = [];
        foreach ($unavailable as $name => $info) {
            if ($info['package']) {
                $options[$info['package']] = "{$name} ({$info['package']}) - {$info['description']}";
            }
        }

        if (empty($options)) {
            return;
        }

        $selected = multiselect(
            label: 'Select drivers to install',
            options: $options,
            hint: 'Use space to select, enter to confirm'
        );

        if (empty($selected)) {
            return;
        }

        $this->newLine();
        $this->components->info('Installing selected packages...');

        foreach ($selected as $package) {
            $this->components->task("Installing {$package}", function () use ($package) {
                $process = proc_open(
                    "composer require {$package}",
                    [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ],
                    $pipes,
                    base_path()
                );

                if (is_resource($process)) {
                    fclose($pipes[0]);
                    stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    $returnCode = proc_close($process);

                    return $returnCode === 0;
                }

                return false;
            });
        }
    }

    protected function showNextSteps(): void
    {
        $this->newLine();
        $this->components->info('Installation complete!');
        $this->newLine();

        $this->line('<fg=cyan>Next steps:</>');
        $this->newLine();
        $this->line('  1. Configure your preferred driver in <fg=yellow>config/dokufy.php</>');
        $this->line('  2. Set environment variables for your chosen driver:');
        $this->newLine();
        $this->line('     <fg=gray># For Gotenberg (Docker)</>');
        $this->line('     <fg=green>DOKUFY_DRIVER=gotenberg</>');
        $this->line('     <fg=green>DOKUFY_GOTENBERG_URL=http://localhost:3000</>');
        $this->newLine();
        $this->line('     <fg=gray># For Chromium (Browsershot)</>');
        $this->line('     <fg=green>DOKUFY_DRIVER=chromium</>');
        $this->newLine();
        $this->line('     <fg=gray># For LibreOffice</>');
        $this->line('     <fg=green>DOKUFY_DRIVER=libreoffice</>');
        $this->newLine();
        $this->line('  3. Create your templates in <fg=yellow>resources/templates/</>');
        $this->line('  4. Generate your first document:');
        $this->newLine();
        $this->line('     <fg=green>php artisan dokufy:generate resources/templates/sample.html output.pdf</>');
        $this->newLine();
        $this->line('  5. Check driver status anytime with:');
        $this->newLine();
        $this->line('     <fg=green>php artisan dokufy:status</>');
        $this->newLine();
    }
}
