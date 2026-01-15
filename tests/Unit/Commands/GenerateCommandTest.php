<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

describe('GenerateCommand', function () {
    it('can generate pdf from html template with fake driver', function () {
        $inputPath = sys_get_temp_dir().'/test-template-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/test-output-'.uniqid().'.pdf';

        File::put($inputPath, '<h1>Hello {{ name }}</h1>');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);

        // Clean up
        File::delete($inputPath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('fails when input file does not exist', function () {
        $exitCode = Artisan::call('dokufy:generate', [
            'input' => '/non/existent/file.html',
            'output' => sys_get_temp_dir().'/output.pdf',
        ]);

        expect($exitCode)->toBe(1);

        $output = Artisan::output();
        expect($output)->toContain('Input file not found');
    });

    it('accepts json data option', function () {
        $inputPath = sys_get_temp_dir().'/data-test-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/data-output-'.uniqid().'.pdf';

        File::put($inputPath, '<h1>Hello {{ name }}</h1>');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--data' => '{"name":"John"}',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);

        // Clean up
        File::delete($inputPath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('accepts data-file option', function () {
        $inputPath = sys_get_temp_dir().'/datafile-test-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/datafile-output-'.uniqid().'.pdf';
        $dataFilePath = sys_get_temp_dir().'/data-'.uniqid().'.json';

        File::put($inputPath, '<h1>Hello {{ name }}</h1>');
        File::put($dataFilePath, '{"name":"Jane"}');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--data-file' => $dataFilePath,
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);

        // Clean up
        File::delete($inputPath);
        File::delete($dataFilePath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('can specify fake driver option', function () {
        $inputPath = sys_get_temp_dir().'/driver-test-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/driver-output-'.uniqid().'.pdf';

        File::put($inputPath, '<h1>Test</h1>');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);

        // Clean up
        File::delete($inputPath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('fails with invalid driver', function () {
        $inputPath = sys_get_temp_dir().'/invalid-driver-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/invalid-output-'.uniqid().'.pdf';

        File::put($inputPath, '<h1>Test</h1>');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'non-existent-driver',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(1);

        $output = Artisan::output();
        expect($output)->toContain('Available drivers');

        // Clean up
        File::delete($inputPath);
    });

    it('can generate docx from template', function () {
        $inputPath = sys_get_temp_dir().'/docx-template-'.uniqid().'.docx';
        $outputPath = sys_get_temp_dir().'/docx-output-'.uniqid().'.docx';

        File::put($inputPath, 'test docx content');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--force' => true,
        ]);

        expect($exitCode)->toBe(0);

        // Clean up
        File::delete($inputPath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('displays success message after generation', function () {
        $inputPath = sys_get_temp_dir().'/success-test-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/success-output-'.uniqid().'.pdf';

        File::put($inputPath, '<h1>Test</h1>');

        Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--force' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('Document generated successfully');

        // Clean up
        File::delete($inputPath);
        if (File::exists($outputPath)) {
            File::delete($outputPath);
        }
    });

    it('handles unsupported output format', function () {
        $inputPath = sys_get_temp_dir().'/format-test-'.uniqid().'.html';
        $outputPath = sys_get_temp_dir().'/format-output-'.uniqid().'.xyz';

        File::put($inputPath, '<h1>Test</h1>');

        $exitCode = Artisan::call('dokufy:generate', [
            'input' => $inputPath,
            'output' => $outputPath,
            '--driver' => 'fake',
            '--force' => true,
        ]);

        expect($exitCode)->toBe(1);

        $output = Artisan::output();
        expect($output)->toContain('Unsupported output format');

        // Clean up
        File::delete($inputPath);
    });
});
