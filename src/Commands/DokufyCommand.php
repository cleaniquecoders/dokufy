<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Commands;

use Illuminate\Console\Command;

class DokufyCommand extends Command
{
    public $signature = 'dokufy';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
