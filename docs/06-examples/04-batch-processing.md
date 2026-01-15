# Batch Processing

Generate multiple documents efficiently using Laravel queues.

## Why Use Batch Processing?

- Avoid timeout issues with large document sets
- Better user experience (no waiting)
- Resilience to failures (retry individual jobs)
- Resource management (process during off-peak hours)

## Basic Queue Job

Create a job for document generation:

```php
<?php

namespace App\Jobs;

use App\Models\Invoice;
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function handle(): void
    {
        $this->invoice->load('items', 'customer');

        $html = view('invoices.pdf', [
            'invoice' => $this->invoice,
            'company' => Company::first(),
        ])->render();

        $path = storage_path("app/invoices/{$this->invoice->number}.pdf");

        Dokufy::html($html)->toPdf($path);

        $this->invoice->update([
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->invoice->update([
            'pdf_error' => $exception->getMessage(),
        ]);
    }
}
```

## Dispatching Multiple Jobs

### Simple Loop

```php
use App\Jobs\GenerateInvoicePdf;
use App\Models\Invoice;

// Generate PDFs for all unpaid invoices
$invoices = Invoice::where('status', 'unpaid')
    ->whereNull('pdf_generated_at')
    ->get();

foreach ($invoices as $invoice) {
    GenerateInvoicePdf::dispatch($invoice);
}
```

### Using Laravel Batch

For tracking progress and handling completion:

```php
use App\Jobs\GenerateInvoicePdf;
use App\Models\Invoice;
use Illuminate\Support\Facades\Bus;

$invoices = Invoice::where('status', 'unpaid')->get();

$jobs = $invoices->map(fn ($invoice) => new GenerateInvoicePdf($invoice));

$batch = Bus::batch($jobs)
    ->then(function ($batch) {
        // All jobs completed successfully
        Log::info('All invoices generated', ['batch_id' => $batch->id]);
    })
    ->catch(function ($batch, $e) {
        // First batch job failure
        Log::error('Invoice generation failed', [
            'batch_id' => $batch->id,
            'error' => $e->getMessage(),
        ]);
    })
    ->finally(function ($batch) {
        // Batch finished (with or without failures)
        Notification::route('slack', config('slack.webhook'))
            ->notify(new BatchCompleteNotification($batch));
    })
    ->name('Generate Invoices')
    ->dispatch();

return response()->json([
    'batch_id' => $batch->id,
    'total_jobs' => $batch->totalJobs,
]);
```

### Check Batch Progress

```php
use Illuminate\Support\Facades\Bus;

$batch = Bus::findBatch($batchId);

return response()->json([
    'id' => $batch->id,
    'name' => $batch->name,
    'total_jobs' => $batch->totalJobs,
    'pending_jobs' => $batch->pendingJobs,
    'processed_jobs' => $batch->processedJobs(),
    'progress' => $batch->progress(),
    'finished' => $batch->finished(),
    'failed_jobs' => $batch->failedJobs,
]);
```

## Bulk Generation Service

Create a service for managing bulk generation:

```php
<?php

namespace App\Services;

use App\Jobs\GenerateInvoicePdf;
use App\Models\Invoice;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class BulkInvoiceGenerator
{
    private array $filters = [];
    private ?string $notifyEmail = null;

    public function forStatus(string $status): self
    {
        $this->filters['status'] = $status;
        return $this;
    }

    public function forMonth(int $year, int $month): self
    {
        $this->filters['month'] = compact('year', 'month');
        return $this;
    }

    public function notifyOnComplete(string $email): self
    {
        $this->notifyEmail = $email;
        return $this;
    }

    public function dispatch(): Batch
    {
        $invoices = $this->getInvoices();
        $jobs = $invoices->map(fn ($invoice) => new GenerateInvoicePdf($invoice));

        $batch = Bus::batch($jobs)
            ->name('Bulk Invoice Generation')
            ->allowFailures();

        if ($this->notifyEmail) {
            $email = $this->notifyEmail;
            $batch->finally(function ($batch) use ($email) {
                Mail::to($email)->send(new BulkGenerationComplete($batch));
            });
        }

        return $batch->dispatch();
    }

    private function getInvoices()
    {
        $query = Invoice::whereNull('pdf_generated_at');

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['month'])) {
            $query->whereYear('created_at', $this->filters['month']['year'])
                  ->whereMonth('created_at', $this->filters['month']['month']);
        }

        return $query->get();
    }
}
```

Usage:

```php
$batch = app(BulkInvoiceGenerator::class)
    ->forStatus('unpaid')
    ->forMonth(2025, 1)
    ->notifyOnComplete('admin@example.com')
    ->dispatch();
```

## Memory-Efficient Processing

For very large datasets, use chunking:

```php
<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class DispatchInvoiceGeneration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public array $filters = []
    ) {}

    public function handle(): void
    {
        Invoice::query()
            ->whereNull('pdf_generated_at')
            ->when(isset($this->filters['status']), function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->chunkById(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    GenerateInvoicePdf::dispatch($invoice);
                }
            });
    }
}
```

## Rate Limiting

Prevent overwhelming external services:

```php
<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimited;

class GenerateInvoicePdf implements ShouldQueue
{
    // ... other code

    public function middleware(): array
    {
        return [new RateLimited('pdf-generation')];
    }
}
```

Configure rate limit in `AppServiceProvider`:

```php
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('pdf-generation', function ($job) {
        return Limit::perMinute(30); // 30 PDFs per minute
    });
}
```

## Progress Tracking UI

Simple Livewire component for tracking:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Bus;
use Livewire\Component;

class BatchProgress extends Component
{
    public string $batchId;

    public function render()
    {
        $batch = Bus::findBatch($this->batchId);

        return view('livewire.batch-progress', [
            'batch' => $batch,
            'progress' => $batch?->progress() ?? 0,
            'finished' => $batch?->finished() ?? false,
        ]);
    }
}
```

```blade
{{-- resources/views/livewire/batch-progress.blade.php --}}
<div wire:poll.1s>
    @if($batch)
        <div class="mb-4">
            <div class="flex justify-between mb-1">
                <span>{{ $batch->processedJobs() }} / {{ $batch->totalJobs }}</span>
                <span>{{ number_format($progress) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all"
                     style="width: {{ $progress }}%"></div>
            </div>
        </div>

        @if($finished)
            <div class="p-4 bg-green-100 text-green-800 rounded">
                Generation complete! {{ $batch->processedJobs() }} documents generated.
                @if($batch->failedJobs > 0)
                    <span class="text-red-600">({{ $batch->failedJobs }} failed)</span>
                @endif
            </div>
        @endif
    @else
        <div class="text-gray-500">Loading...</div>
    @endif
</div>
```

## Error Handling and Retry

```php
<?php

namespace App\Jobs;

use CleaniqueCoders\Dokufy\Exceptions\ConversionException;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateInvoicePdf implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    public function handle(): void
    {
        try {
            // Generation logic
        } catch (ConversionException $e) {
            // Log and re-throw for retry
            Log::warning('PDF generation failed, will retry', [
                'invoice' => $this->invoice->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
```

## Next Steps

- [Testing](../07-testing/README.md) - Testing batch jobs
- [Drivers](../03-drivers/README.md) - Driver performance considerations
