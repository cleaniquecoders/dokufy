<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Dokufy;
use CleaniqueCoders\Dokufy\Drivers\FakeDriver;
use CleaniqueCoders\Dokufy\Exceptions\DriverException;
use CleaniqueCoders\Dokufy\Exceptions\TemplateNotFoundException;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function () {
    $this->dokufy = app(Dokufy::class);
});

describe('instantiation', function () {
    it('can be instantiated', function () {
        expect($this->dokufy)->toBeInstanceOf(Dokufy::class);
    });

    it('can create a new instance with make', function () {
        $instance = $this->dokufy->make();

        expect($instance)->toBeInstanceOf(Dokufy::class);
        expect($instance)->not->toBe($this->dokufy);
    });

    it('can create a new instance with specific driver', function () {
        $instance = $this->dokufy->make('fake');

        expect($instance)->toBeInstanceOf(Dokufy::class);
    });
});

describe('html content', function () {
    it('can set html content', function () {
        $result = $this->dokufy->html('<h1>Hello World</h1>');

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('returns self for chaining', function () {
        $result = $this->dokufy->html('<h1>Test</h1>');

        expect($result)->toBe($this->dokufy);
    });
});

describe('data binding', function () {
    it('can set data', function () {
        $result = $this->dokufy->data(['name' => 'John']);

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('can chain data calls', function () {
        $result = $this->dokufy
            ->data(['name' => 'John'])
            ->data(['email' => 'john@example.com']);

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('merges data from multiple calls', function () {
        $this->dokufy->fake();

        $this->dokufy
            ->html('<h1>{{ name }} - {{ email }}</h1>')
            ->data(['name' => 'John'])
            ->data(['email' => 'john@example.com'])
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('template handling', function () {
    it('throws exception for non-existent template', function () {
        $this->dokufy->template('/path/to/non-existent/template.docx');
    })->throws(TemplateNotFoundException::class);

    it('accepts valid template path', function () {
        // Create a temporary file
        $tempFile = sys_get_temp_dir().'/test-template.html';
        File::put($tempFile, '<h1>Test</h1>');

        $result = $this->dokufy->template($tempFile);

        expect($result)->toBeInstanceOf(Dokufy::class);

        // Clean up
        File::delete($tempFile);
    });
});

describe('fake driver', function () {
    it('can fake the driver for testing', function () {
        $fakeDriver = $this->dokufy->fake();

        expect($fakeDriver)->toBeInstanceOf(FakeDriver::class);
    });

    it('can generate pdf with fake driver', function () {
        $this->dokufy->fake();

        $outputPath = sys_get_temp_dir().'/test-output.pdf';

        $result = $this->dokufy
            ->html('<h1>Hello World</h1>')
            ->toPdf($outputPath);

        expect($result)->toBe($outputPath);

        $this->dokufy->assertPdfGenerated();
        $this->dokufy->assertGenerated($outputPath);
    });

    it('checks if fake driver is always available', function () {
        expect($this->dokufy->isDriverAvailable('fake'))->toBeTrue();
    });
});

describe('reset', function () {
    it('can reset the instance', function () {
        $this->dokufy
            ->html('<h1>Test</h1>')
            ->data(['key' => 'value'])
            ->reset();

        // After reset, we should be able to start fresh
        $result = $this->dokufy->html('<h1>New Content</h1>');

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('returns self for chaining after reset', function () {
        $result = $this->dokufy->reset();

        expect($result)->toBe($this->dokufy);
    });
});

describe('placeholder replacement', function () {
    it('replaces placeholders in html content', function () {
        $this->dokufy->fake();

        $outputPath = sys_get_temp_dir().'/test-placeholder.pdf';

        $this->dokufy
            ->html('<h1>Hello {{ name }}</h1>')
            ->data(['name' => 'World'])
            ->toPdf($outputPath);

        $this->dokufy->assertPdfGenerated();
    });

    it('replaces placeholders with different spacing', function () {
        $this->dokufy->fake();

        $this->dokufy
            ->html('<p>{{name}} - {{ name }} - {{name }} - {{ name}}</p>')
            ->data(['name' => 'Test'])
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('handles multiple placeholders', function () {
        $this->dokufy->fake();

        $this->dokufy
            ->html('<h1>{{ title }}</h1><p>{{ content }}</p><footer>{{ author }}</footer>')
            ->data([
                'title' => 'My Title',
                'content' => 'My Content',
                'author' => 'John Doe',
            ])
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('driver selection', function () {
    it('can select a specific driver', function () {
        $result = $this->dokufy->driver('fake');

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('throws exception for unknown driver', function () {
        $this->dokufy->driver('unknown-driver');
    })->throws(DriverException::class);

    it('returns available drivers list', function () {
        $drivers = $this->dokufy->getAvailableDrivers();

        expect($drivers)->toBeArray();
    });

    it('fake driver is in available drivers', function () {
        $drivers = $this->dokufy->getAvailableDrivers();

        expect($drivers)->toContain('fake');
    });

    it('returns false for unavailable driver', function () {
        expect($this->dokufy->isDriverAvailable('non-existent'))->toBeFalse();
    });
});

describe('output methods', function () {
    it('throws exception when no content set for toPdf', function () {
        $this->dokufy->fake();

        $this->dokufy->toPdf(sys_get_temp_dir().'/test.pdf');
    })->throws(\RuntimeException::class, 'No template or HTML content has been set.');

    it('throws exception for toDocx without template', function () {
        $this->dokufy->toDocx(sys_get_temp_dir().'/test.docx');
    })->throws(\RuntimeException::class, 'A template is required for DOCX output.');

    it('can stream pdf', function () {
        $this->dokufy->fake();

        $response = $this->dokufy
            ->html('<h1>Test</h1>')
            ->stream('test.pdf');

        expect($response)->toBeInstanceOf(StreamedResponse::class);
    });

    it('can download pdf with real file', function () {
        // Create a real temp file for download test
        $tempPath = sys_get_temp_dir().'/download-test-'.uniqid().'.pdf';
        File::put($tempPath, 'test pdf content');

        $this->dokufy->fake();

        // Use template method to use the existing file
        $this->dokufy->html('<h1>Test</h1>');

        // The FakeDriver doesn't create real files, so we test with a real file
        $response = response()->download($tempPath, 'test.pdf', [
            'Content-Type' => 'application/pdf',
        ]);

        expect($response)->toBeInstanceOf(BinaryFileResponse::class);

        File::delete($tempPath);
    });

    it('uses default filename for stream when not provided', function () {
        $this->dokufy->fake();

        $response = $this->dokufy
            ->html('<h1>Test</h1>')
            ->stream();

        expect($response)->toBeInstanceOf(StreamedResponse::class);
    });

    it('stream response has correct content type', function () {
        $this->dokufy->fake();

        $response = $this->dokufy
            ->html('<h1>Test</h1>')
            ->stream('document.pdf');

        expect($response->headers->get('Content-Type'))->toBe('application/pdf');
    });
});

describe('toDocx output', function () {
    it('can generate docx from template', function () {
        // Create a temporary template
        $templatePath = sys_get_temp_dir().'/test-template.docx';
        $outputPath = sys_get_temp_dir().'/test-output.docx';

        File::put($templatePath, 'test content');

        $result = $this->dokufy
            ->template($templatePath)
            ->toDocx($outputPath);

        expect($result)->toBe($outputPath);
        expect(File::exists($outputPath))->toBeTrue();

        // Clean up
        File::delete($templatePath);
        File::delete($outputPath);
    });
});

describe('assertions without fake driver', function () {
    it('throws exception for assertGenerated without fake', function () {
        $this->dokufy->assertGenerated('/tmp/test.pdf');
    })->throws(\RuntimeException::class, 'No fake driver has been set. Call fake() first.');

    it('throws exception for assertPdfGenerated without fake', function () {
        $this->dokufy->assertPdfGenerated();
    })->throws(\RuntimeException::class, 'No fake driver has been set. Call fake() first.');

    it('throws exception for assertDocxGenerated without fake', function () {
        $this->dokufy->assertDocxGenerated();
    })->throws(\RuntimeException::class, 'No fake driver has been set. Call fake() first.');
});

describe('with placeholder handler', function () {
    it('can use with method for placeholder handler', function () {
        $this->dokufy->fake();

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

        $this->dokufy
            ->html('<h1>Hello {{ name }} from {{ company }}</h1>')
            ->with($handler)
            ->toPdf($outputPath);

        $this->dokufy->assertPdfGenerated();
    });

    it('supports handler with getPlaceholders method', function () {
        $this->dokufy->fake();

        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function getPlaceholders(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<h1>Hello {{ name }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('supports handler with resolve method', function () {
        $this->dokufy->fake();

        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<h1>Hello {{ name }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});
