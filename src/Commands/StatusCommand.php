<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Commands;

use CleaniqueCoders\Dokufy\Dokufy;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    /**
     * @var string
     */
    public $signature = 'dokufy:status';

    /**
     * @var string
     */
    public $description = 'Check the status and availability of Dokufy drivers';

    public function handle(Dokufy $dokufy): int
    {
        $this->components->info('Dokufy Driver Status');
        $this->newLine();

        $drivers = [
            'gotenberg' => [
                'name' => 'Gotenberg',
                'description' => 'Docker-based PDF generation via Gotenberg API',
                'package' => 'gotenberg/gotenberg-php',
            ],
            'libreoffice' => [
                'name' => 'LibreOffice',
                'description' => 'CLI-based conversion using LibreOffice headless mode',
                'package' => null,
            ],
            'chromium' => [
                'name' => 'Chromium',
                'description' => 'Browser-based PDF generation via Browsershot',
                'package' => 'spatie/browsershot',
            ],
            'phpword' => [
                'name' => 'PHPWord',
                'description' => 'Native PHP DOCX processing with PDF export',
                'package' => 'phpoffice/phpword',
            ],
            'fake' => [
                'name' => 'Fake',
                'description' => 'Testing driver for unit tests',
                'package' => null,
            ],
        ];

        $rows = [];
        $availableCount = 0;

        foreach ($drivers as $key => $info) {
            $isAvailable = $dokufy->isDriverAvailable($key);

            if ($isAvailable) {
                $availableCount++;
            }

            $rows[] = [
                $info['name'],
                $key,
                $isAvailable ? '<fg=green>✓ Available</>' : '<fg=red>✗ Not Available</>',
                $info['package'] ?? '-',
            ];
        }

        $this->table(
            ['Driver', 'Key', 'Status', 'Package'],
            $rows
        );

        $this->newLine();

        /** @var string $defaultDriver */
        $defaultDriver = config('dokufy.default', 'gotenberg');
        $this->components->twoColumnDetail('Default Driver', $defaultDriver);
        $this->components->twoColumnDetail('Available Drivers', (string) $availableCount.'/'.count($drivers));

        $this->newLine();

        if ($availableCount === 0) {
            $this->components->warn('No drivers are currently available. Please install the required packages.');

            return self::FAILURE;
        }

        if (! $dokufy->isDriverAvailable($defaultDriver)) {
            $this->components->warn("Default driver [{$defaultDriver}] is not available.");
            $this->components->info('Available drivers: '.implode(', ', $dokufy->getAvailableDrivers()));

            return self::FAILURE;
        }

        $this->components->success('Dokufy is ready to use!');

        return self::SUCCESS;
    }
}
