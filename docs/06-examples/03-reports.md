# Report Generation

Generate data-driven reports using Blade views and Dokufy.

## Basic Report

### Blade View

Create `resources/views/reports/monthly-sales.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            width: 22%;
            text-align: center;
        }
        .summary-box .value {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
        }
        .summary-box .label {
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 500;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row {
            font-weight: bold;
            background: #f1f5f9 !important;
        }
        .chart-placeholder {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Monthly Sales Report</h1>
        <p>{{ $period }}</p>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="value">{{ $summary['total_orders'] }}</div>
            <div class="label">Total Orders</div>
        </div>
        <div class="summary-box">
            <div class="value">RM {{ number_format($summary['total_revenue'], 2) }}</div>
            <div class="label">Total Revenue</div>
        </div>
        <div class="summary-box">
            <div class="value">RM {{ number_format($summary['average_order'], 2) }}</div>
            <div class="label">Average Order</div>
        </div>
        <div class="summary-box">
            <div class="value">{{ $summary['new_customers'] }}</div>
            <div class="label">New Customers</div>
        </div>
    </div>

    <h2>Sales by Product Category</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-center">Units Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $category['name'] }}</td>
                <td class="text-center">{{ number_format($category['units']) }}</td>
                <td class="text-right">RM {{ number_format($category['revenue'], 2) }}</td>
                <td class="text-right">{{ number_format($category['percentage'], 1) }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td class="text-center">{{ number_format($totals['units']) }}</td>
                <td class="text-right">RM {{ number_format($totals['revenue'], 2) }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tbody>
    </table>

    <h2>Top Performing Products</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="text-center">Units</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $product['name'] }}</td>
                <td class="text-center">{{ number_format($product['units']) }}</td>
                <td class="text-right">RM {{ number_format($product['revenue'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d F Y, H:i') }} | {{ config('app.name') }}
    </div>
</body>
</html>
```

### Report Service

```php
<?php

namespace App\Services\Reports;

use App\Models\Order;
use App\Models\Customer;
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use Carbon\Carbon;

class MonthlySalesReport
{
    private Carbon $startDate;
    private Carbon $endDate;

    public function forMonth(int $year, int $month): self
    {
        $this->startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $this->endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return $this;
    }

    public function generate(): string
    {
        $data = $this->collectData();
        $html = view('reports.monthly-sales', $data)->render();

        $filename = "sales-report-{$this->startDate->format('Y-m')}.pdf";
        $path = storage_path("app/reports/{$filename}");

        Dokufy::html($html)->toPdf($path);

        return $path;
    }

    public function download()
    {
        $data = $this->collectData();
        $html = view('reports.monthly-sales', $data)->render();

        $filename = "sales-report-{$this->startDate->format('Y-m')}.pdf";

        return Dokufy::html($html)->download($filename);
    }

    private function collectData(): array
    {
        return [
            'period' => $this->startDate->format('F Y'),
            'summary' => $this->getSummary(),
            'categories' => $this->getCategoryBreakdown(),
            'totals' => $this->getTotals(),
            'topProducts' => $this->getTopProducts(),
        ];
    }

    private function getSummary(): array
    {
        $orders = Order::whereBetween('created_at', [$this->startDate, $this->endDate]);

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
            'average_order' => $orders->avg('total') ?? 0,
            'new_customers' => Customer::whereBetween('created_at', [$this->startDate, $this->endDate])->count(),
        ];
    }

    private function getCategoryBreakdown(): array
    {
        // Implementation depends on your data model
        return Order::query()
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(order_items.quantity) as units, SUM(order_items.total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(function ($item) use (&$totalRevenue) {
                $totalRevenue = $totalRevenue ?? Order::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('total');
                return [
                    'name' => $item->name,
                    'units' => $item->units,
                    'revenue' => $item->revenue,
                    'percentage' => $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0,
                ];
            })
            ->toArray();
    }

    private function getTotals(): array
    {
        $orders = Order::whereBetween('created_at', [$this->startDate, $this->endDate]);

        return [
            'units' => $orders->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->sum('order_items.quantity'),
            'revenue' => $orders->sum('total'),
        ];
    }

    private function getTopProducts(int $limit = 10): array
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(order_items.quantity) as units, SUM(order_items.total) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
```

### Controller

```php
<?php

namespace App\Http\Controllers\Reports;

use App\Services\Reports\MonthlySalesReport;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function __construct(
        private MonthlySalesReport $report
    ) {}

    public function download(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        return $this->report
            ->forMonth($request->year, $request->month)
            ->download();
    }

    public function generate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $path = $this->report
            ->forMonth($request->year, $request->month)
            ->generate();

        return response()->json([
            'message' => 'Report generated successfully',
            'path' => $path,
        ]);
    }
}
```

## Scheduled Report Generation

Generate reports automatically via Laravel scheduler:

```php
<?php

namespace App\Console\Commands;

use App\Services\Reports\MonthlySalesReport;
use Illuminate\Console\Command;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'reports:monthly-sales {--year=} {--month=}';
    protected $description = 'Generate monthly sales report';

    public function handle(MonthlySalesReport $report): int
    {
        $year = $this->option('year') ?? now()->subMonth()->year;
        $month = $this->option('month') ?? now()->subMonth()->month;

        $this->info("Generating report for {$year}-{$month}...");

        $path = $report->forMonth($year, $month)->generate();

        $this->info("Report generated: {$path}");

        return self::SUCCESS;
    }
}
```

Schedule in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Generate previous month's report on the 1st of each month
    $schedule->command('reports:monthly-sales')
        ->monthlyOn(1, '06:00')
        ->emailOutputOnFailure('admin@example.com');
}
```

## Next Steps

- [Batch Processing](04-batch-processing.md) - Generate reports in bulk
- [API Reference](../04-api/README.md) - All available methods
