<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

describe('InstallCommand', function () {
    beforeEach(function () {
        // Clean up any previous test artifacts
        $templatesPath = resource_path('templates');
        if (File::isDirectory($templatesPath)) {
            File::deleteDirectory($templatesPath);
        }
    });

    afterEach(function () {
        // Clean up after tests
        $templatesPath = resource_path('templates');
        if (File::isDirectory($templatesPath)) {
            File::deleteDirectory($templatesPath);
        }
    });

    it('can be executed', function () {
        // Run in non-interactive mode by providing --no-interaction
        $exitCode = Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        expect($exitCode)->toBe(0);
    });

    it('displays installation information', function () {
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        $output = Artisan::output();

        expect($output)->toContain('Installing Dokufy');
    });

    it('shows next steps after installation', function () {
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        $output = Artisan::output();

        expect($output)->toContain('Next steps');
        expect($output)->toContain('config/dokufy.php');
    });

    it('creates templates directory', function () {
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        $templatesPath = resource_path('templates');

        expect(File::isDirectory($templatesPath))->toBeTrue();
    });

    it('creates sample template file', function () {
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        $samplePath = resource_path('templates/sample.html');

        expect(File::exists($samplePath))->toBeTrue();

        $content = File::get($samplePath);
        expect($content)->toContain('{{ title }}');
        expect($content)->toContain('{{ name }}');
    });

    it('shows driver availability', function () {
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        $output = Artisan::output();

        expect($output)->toContain('Checking available drivers');
    });

    it('accepts force option', function () {
        // First install
        Artisan::call('dokufy:install', [
            '--no-interaction' => true,
        ]);

        // Second install with force
        $exitCode = Artisan::call('dokufy:install', [
            '--force' => true,
            '--no-interaction' => true,
        ]);

        expect($exitCode)->toBe(0);
    });
});
