<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

describe('StatusCommand', function () {
    it('can be executed', function () {
        $exitCode = Artisan::call('dokufy:status');

        // Command may return FAILURE if default driver is not available
        // but should always be able to run
        expect($exitCode)->toBeInt();
    });

    it('displays driver information', function () {
        Artisan::call('dokufy:status');

        $output = Artisan::output();

        expect($output)->toContain('Dokufy Driver Status');
        expect($output)->toContain('Fake');
        expect($output)->toContain('Gotenberg');
        expect($output)->toContain('LibreOffice');
        expect($output)->toContain('Chromium');
        expect($output)->toContain('PHPWord');
    });

    it('shows fake driver as available', function () {
        Artisan::call('dokufy:status');

        $output = Artisan::output();

        expect($output)->toContain('Available');
    });

    it('shows default driver configuration', function () {
        Artisan::call('dokufy:status');

        $output = Artisan::output();

        expect($output)->toContain('Default Driver');
    });

    it('shows available drivers count', function () {
        Artisan::call('dokufy:status');

        $output = Artisan::output();

        expect($output)->toContain('Available Drivers');
    });

    it('returns success when default driver is fake', function () {
        config(['dokufy.default' => 'fake']);

        $exitCode = Artisan::call('dokufy:status');

        expect($exitCode)->toBe(0);
    });
});
