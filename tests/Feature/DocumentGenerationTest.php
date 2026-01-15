<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Dokufy;
use CleaniqueCoders\Dokufy\Facades\Dokufy as DokufyFacade;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

describe('facade usage', function () {
    it('can generate pdf from html using facade', function () {
        DokufyFacade::fake();

        $outputPath = sys_get_temp_dir().'/facade-test.pdf';

        DokufyFacade::html('<h1>Hello from Facade</h1>')
            ->toPdf($outputPath);

        DokufyFacade::assertPdfGenerated();
        DokufyFacade::assertGenerated($outputPath);
    });

    it('facade returns Dokufy instance', function () {
        $instance = DokufyFacade::getFacadeRoot();

        expect($instance)->toBeInstanceOf(Dokufy::class);
    });
});

describe('method chaining', function () {
    it('can chain data and html methods', function () {
        DokufyFacade::fake();

        $outputPath = sys_get_temp_dir().'/chained-test.pdf';

        DokufyFacade::html('<h1>Hello {{ name }}</h1>')
            ->data(['name' => 'World'])
            ->toPdf($outputPath);

        DokufyFacade::assertPdfGenerated();
    });

    it('can chain multiple data calls', function () {
        DokufyFacade::fake();

        $outputPath = sys_get_temp_dir().'/multi-data.pdf';

        DokufyFacade::html('<h1>{{ title }}</h1><p>{{ content }}</p>')
            ->data(['title' => 'Hello'])
            ->data(['content' => 'World'])
            ->toPdf($outputPath);

        DokufyFacade::assertPdfGenerated();
    });

    it('can chain html then data then toPdf', function () {
        DokufyFacade::fake();

        $result = DokufyFacade::html('<p>Test</p>')
            ->data(['key' => 'value'])
            ->toPdf(sys_get_temp_dir().'/chain-order.pdf');

        expect($result)->toEndWith('.pdf');
    });
});

describe('instance creation', function () {
    it('can use make to create new instances', function () {
        $instance = DokufyFacade::make('fake');

        $outputPath = sys_get_temp_dir().'/make-test.pdf';

        $result = $instance
            ->html('<h1>New Instance</h1>')
            ->toPdf($outputPath);

        expect($result)->toBe($outputPath);
    });

    it('make creates independent instances', function () {
        $instance1 = DokufyFacade::make('fake');
        $instance2 = DokufyFacade::make('fake');

        expect($instance1)->not->toBe($instance2);
    });

    it('make with default driver works', function () {
        $instance = DokufyFacade::make();

        expect($instance)->toBeInstanceOf(Dokufy::class);
    });
});

describe('reset functionality', function () {
    it('can reset and reuse facade', function () {
        DokufyFacade::fake();

        // First use
        DokufyFacade::html('<h1>First</h1>')
            ->data(['key' => 'value1'])
            ->toPdf(sys_get_temp_dir().'/first.pdf');

        DokufyFacade::reset();

        // After reset, need to fake again since reset clears the driver
        DokufyFacade::fake();

        // Second use
        DokufyFacade::html('<h1>Second</h1>')
            ->data(['key' => 'value2'])
            ->toPdf(sys_get_temp_dir().'/second.pdf');

        DokufyFacade::assertPdfGenerated();
    });

    it('reset clears previous data', function () {
        DokufyFacade::fake();

        DokufyFacade::html('<p>Content</p>')->data(['key' => 'value']);
        DokufyFacade::reset();
        DokufyFacade::fake();

        // Should be able to start fresh
        $result = DokufyFacade::html('<p>New</p>')->toPdf(sys_get_temp_dir().'/reset.pdf');

        expect($result)->toEndWith('.pdf');
    });
});

describe('driver availability', function () {
    it('can check driver availability', function () {
        expect(DokufyFacade::isDriverAvailable('fake'))->toBeTrue();
    });

    it('returns false for non-existent driver', function () {
        expect(DokufyFacade::isDriverAvailable('non-existent'))->toBeFalse();
    });

    it('returns available drivers list', function () {
        $drivers = DokufyFacade::getAvailableDrivers();

        expect($drivers)->toBeArray();
        expect($drivers)->toContain('fake');
    });

    it('available drivers list includes all registered drivers', function () {
        $drivers = DokufyFacade::getAvailableDrivers();

        expect($drivers)->toContain('fake');
        // Other drivers may or may not be available depending on packages
    });
});

describe('placeholder handler', function () {
    it('can use with method for placeholder handler', function () {
        DokufyFacade::fake();

        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test', 'company' => 'Acme Inc'];
            }
        };

        $outputPath = sys_get_temp_dir().'/handler-test.pdf';

        DokufyFacade::html('<h1>Hello {{ name }} from {{ company }}</h1>')
            ->with($handler)
            ->toPdf($outputPath);

        DokufyFacade::assertPdfGenerated();
    });

    it('works with getPlaceholders handler', function () {
        DokufyFacade::fake();

        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function getPlaceholders(): array
            {
                return ['title' => 'Document Title'];
            }
        };

        DokufyFacade::html('<h1>{{ title }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/getplaceholders.pdf');

        DokufyFacade::assertPdfGenerated();
    });

    it('works with resolve handler', function () {
        DokufyFacade::fake();

        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['greeting' => 'Hello World'];
            }
        };

        DokufyFacade::html('<p>{{ greeting }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/resolve.pdf');

        DokufyFacade::assertPdfGenerated();
    });
});

describe('template handling', function () {
    it('can use template file', function () {
        $templatePath = sys_get_temp_dir().'/test-template.html';
        File::put($templatePath, '<h1>{{ title }}</h1>');

        DokufyFacade::fake();

        $result = DokufyFacade::template($templatePath)
            ->data(['title' => 'Test'])
            ->toPdf(sys_get_temp_dir().'/template-output.pdf');

        expect($result)->toEndWith('.pdf');

        File::delete($templatePath);
    });
});

describe('output responses', function () {
    it('can stream pdf', function () {
        DokufyFacade::fake();

        $response = DokufyFacade::html('<h1>Test</h1>')
            ->stream('test.pdf');

        expect($response)->toBeInstanceOf(StreamedResponse::class);
    });

    it('can download pdf with real file', function () {
        // Create a real temp file for download test
        $tempPath = sys_get_temp_dir().'/download-feature-test-'.uniqid().'.pdf';
        File::put($tempPath, 'test pdf content');

        // Test Laravel's download response
        $response = response()->download($tempPath, 'test.pdf', [
            'Content-Type' => 'application/pdf',
        ]);

        expect($response)->toBeInstanceOf(BinaryFileResponse::class);

        File::delete($tempPath);
    });

    it('stream uses default filename when not provided', function () {
        DokufyFacade::fake();

        $response = DokufyFacade::html('<h1>Test</h1>')
            ->stream();

        expect($response)->toBeInstanceOf(StreamedResponse::class);
    });

    it('stream response has pdf content type', function () {
        DokufyFacade::fake();

        $response = DokufyFacade::html('<h1>Test</h1>')
            ->stream('output.pdf');

        expect($response->headers->get('Content-Type'))->toBe('application/pdf');
    });
});

describe('driver selection', function () {
    it('can select fake driver explicitly', function () {
        $result = DokufyFacade::driver('fake');

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('driver method returns self for chaining', function () {
        $result = DokufyFacade::driver('fake')
            ->html('<h1>Test</h1>')
            ->toPdf(sys_get_temp_dir().'/driver-chain.pdf');

        expect($result)->toEndWith('.pdf');
    });
});

describe('docx generation', function () {
    it('can generate docx from template', function () {
        $templatePath = sys_get_temp_dir().'/docx-template.docx';
        $outputPath = sys_get_temp_dir().'/docx-output.docx';

        File::put($templatePath, 'test content');

        $result = DokufyFacade::template($templatePath)
            ->toDocx($outputPath);

        expect($result)->toBe($outputPath);
        expect(File::exists($outputPath))->toBeTrue();

        File::delete($templatePath);
        File::delete($outputPath);
    });
});

describe('assertions', function () {
    it('assertPdfGenerated passes when pdf generated', function () {
        DokufyFacade::fake();

        DokufyFacade::html('<p>Test</p>')
            ->toPdf(sys_get_temp_dir().'/assert-test.pdf');

        // This should pass without throwing
        DokufyFacade::assertPdfGenerated();

        expect(true)->toBeTrue();
    });

    it('assertGenerated passes for specific path', function () {
        DokufyFacade::fake();

        $path = sys_get_temp_dir().'/specific-path.pdf';

        DokufyFacade::html('<p>Test</p>')
            ->toPdf($path);

        // This should pass without throwing
        DokufyFacade::assertGenerated($path);

        expect(true)->toBeTrue();
    });
});
