# Offer Letter Generation

Generate HR documents like offer letters, employment contracts, and appointment letters.

## Basic Offer Letter

### DOCX Template

Create `resources/templates/offer-letter.docx`:

```text
[Company Logo]

{{ date }}

{{ employee_name }}
{{ employee_address }}

Dear {{ employee_name }},

RE: OFFER OF EMPLOYMENT - {{ position }}

We are pleased to offer you the position of {{ position }} at {{ company_name }}.

The terms of your employment are as follows:

1. Position: {{ position }}
2. Department: {{ department }}
3. Reporting To: {{ reporting_to }}
4. Start Date: {{ start_date }}
5. Basic Salary: {{ salary }} per month
6. Working Hours: {{ working_hours }}

Benefits:
{{ benefits }}

Please sign and return this letter by {{ response_deadline }} to confirm your acceptance.

We look forward to welcoming you to our team.

Yours sincerely,

____________________
{{ hr_manager_name }}
{{ hr_manager_title }}
{{ company_name }}

ACCEPTANCE

I, {{ employee_name }}, accept the above offer of employment.

Signature: ____________________
Date: ____________________
```

### Generate Offer Letter

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;

$employee = Employee::find($id);
$company = Company::first();

Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->data([
        'date' => now()->format('d F Y'),
        'employee_name' => $employee->name,
        'employee_address' => $employee->full_address,
        'position' => $employee->position->title,
        'department' => $employee->department->name,
        'reporting_to' => $employee->manager->name,
        'start_date' => $employee->start_date->format('d F Y'),
        'salary' => 'RM ' . number_format($employee->salary, 2),
        'working_hours' => '9:00 AM - 6:00 PM, Monday to Friday',
        'benefits' => $employee->getBenefitsList(),
        'response_deadline' => now()->addDays(7)->format('d F Y'),
        'hr_manager_name' => $company->hr_manager,
        'hr_manager_title' => 'Human Resources Manager',
        'company_name' => $company->name,
    ])
    ->toPdf(storage_path("hr/offer-letters/{$employee->id}.pdf"));
```

## Using Placeholdify

For complex data binding with models:

```php
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$employee = Employee::with(['position', 'department', 'manager'])->find($id);
$company = Company::first();

$handler = (new PlaceholderHandler)
    // Employee context with 'emp' prefix
    ->useContext('employee', $employee, 'emp')

    // Position context with 'pos' prefix
    ->useContext('position', $employee->position, 'pos')

    // Company context with 'co' prefix
    ->useContext('company', $company, 'co')

    // Formatted values
    ->addFormatted('salary', $employee->salary, 'currency', 'MYR')
    ->addDate('start_date', $employee->start_date, 'd F Y')
    ->addDate('response_deadline', now()->addDays(7), 'd F Y');

Dokufy::template(resource_path('templates/offer-letter.docx'))
    ->with($handler)
    ->toPdf(storage_path("hr/offer-letters/{$employee->id}.pdf"));
```

## Offer Letter Service

```php
<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\Company;
use CleaniqueCoders\Dokufy\Facades\Dokufy;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class OfferLetterGenerator
{
    private Employee $employee;
    private Company $company;

    public function for(Employee $employee): self
    {
        $this->employee = $employee->load(['position', 'department', 'manager']);
        $this->company = Company::first();

        return $this;
    }

    public function generate(): string
    {
        $handler = $this->buildHandler();
        $path = $this->getOutputPath();

        Dokufy::template($this->getTemplate())
            ->with($handler)
            ->toPdf($path);

        return $path;
    }

    public function download()
    {
        $handler = $this->buildHandler();

        return Dokufy::template($this->getTemplate())
            ->with($handler)
            ->download("offer-letter-{$this->employee->employee_id}.pdf");
    }

    private function buildHandler(): PlaceholderHandler
    {
        return (new PlaceholderHandler)
            ->useContext('employee', $this->employee, 'emp')
            ->useContext('position', $this->employee->position, 'pos')
            ->useContext('department', $this->employee->department, 'dept')
            ->useContext('company', $this->company, 'co')
            ->add('date', now()->format('d F Y'))
            ->addFormatted('salary', $this->employee->salary, 'currency', 'MYR')
            ->addDate('start_date', $this->employee->start_date, 'd F Y')
            ->addDate('response_deadline', now()->addDays(7), 'd F Y');
    }

    private function getTemplate(): string
    {
        return resource_path('templates/offer-letter.docx');
    }

    private function getOutputPath(): string
    {
        return storage_path("app/hr/offer-letters/{$this->employee->id}.pdf");
    }
}
```

## Controller Implementation

```php
<?php

namespace App\Http\Controllers\HR;

use App\Models\Employee;
use App\Services\HR\OfferLetterGenerator;
use Illuminate\Http\Request;

class OfferLetterController extends Controller
{
    public function __construct(
        private OfferLetterGenerator $generator
    ) {}

    public function preview(Employee $employee)
    {
        $this->authorize('viewOfferLetter', $employee);

        return $this->generator
            ->for($employee)
            ->download();
    }

    public function generate(Employee $employee)
    {
        $this->authorize('generateOfferLetter', $employee);

        $path = $this->generator
            ->for($employee)
            ->generate();

        $employee->update([
            'offer_letter_generated_at' => now(),
            'offer_letter_path' => $path,
        ]);

        return back()->with('success', 'Offer letter generated successfully');
    }

    public function sendToEmployee(Employee $employee)
    {
        $this->authorize('sendOfferLetter', $employee);

        $path = $this->generator->for($employee)->generate();

        Mail::to($employee->personal_email)
            ->send(new OfferLetterMail($employee, $path));

        $employee->update(['offer_letter_sent_at' => now()]);

        return back()->with('success', 'Offer letter sent to employee');
    }
}
```

## Multiple Document Types

Create a factory for different HR documents:

```php
<?php

namespace App\Services\HR;

use App\Models\Employee;

class HRDocumentFactory
{
    public static function make(string $type, Employee $employee): HRDocumentGenerator
    {
        return match ($type) {
            'offer-letter' => new OfferLetterGenerator($employee),
            'appointment-letter' => new AppointmentLetterGenerator($employee),
            'confirmation-letter' => new ConfirmationLetterGenerator($employee),
            'termination-letter' => new TerminationLetterGenerator($employee),
            default => throw new \InvalidArgumentException("Unknown document type: {$type}"),
        };
    }
}

// Usage
$document = HRDocumentFactory::make('offer-letter', $employee);
$path = $document->generate();
```

## Testing

```php
<?php

use App\Models\Employee;
use App\Services\HR\OfferLetterGenerator;
use CleaniqueCoders\Dokufy\Facades\Dokufy;

beforeEach(function () {
    Dokufy::fake();
});

it('generates offer letter for employee', function () {
    $employee = Employee::factory()
        ->has(Position::factory())
        ->has(Department::factory())
        ->create([
            'salary' => 8500,
            'start_date' => now()->addMonth(),
        ]);

    app(OfferLetterGenerator::class)
        ->for($employee)
        ->generate();

    Dokufy::assertPdfGenerated();
});

it('uses correct template', function () {
    $employee = Employee::factory()->create();

    app(OfferLetterGenerator::class)
        ->for($employee)
        ->generate();

    Dokufy::assertTemplateUsed(resource_path('templates/offer-letter.docx'));
});
```

## Next Steps

- [Reports](03-reports.md) - Data-driven report generation
- [Batch Processing](04-batch-processing.md) - Bulk document generation
