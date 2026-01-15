<?php

// config for CleaniqueCoders/Dokufy

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default document generation driver that will be
    | used by the package. You may set this to any of the drivers defined in
    | the "drivers" array below.
    |
    | Supported: "gotenberg", "libreoffice", "chromium", "phpword", "fake"
    |
    */
    'default' => env('DOKUFY_DRIVER', 'phpword'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure each of the document generation drivers used by
    | your application. Each driver has its own specific configuration options.
    |
    */
    'drivers' => [
        'gotenberg' => [
            'url' => env('DOKUFY_GOTENBERG_URL', 'http://gotenberg:3000'),
            'timeout' => env('DOKUFY_GOTENBERG_TIMEOUT', 120),
        ],

        'libreoffice' => [
            'binary' => env('DOKUFY_LIBREOFFICE_BINARY', 'libreoffice'),
            'timeout' => env('DOKUFY_LIBREOFFICE_TIMEOUT', 120),
        ],

        'chromium' => [
            'node_binary' => env('DOKUFY_NODE_BINARY'),
            'npm_binary' => env('DOKUFY_NPM_BINARY'),
            'timeout' => env('DOKUFY_CHROMIUM_TIMEOUT', 60),
        ],

        'phpword' => [
            'pdf_renderer' => env('DOKUFY_PDF_RENDERER', 'dompdf'), // dompdf, tcpdf, mpdf
        ],

        'fake' => [
            // No configuration needed for fake driver
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Options
    |--------------------------------------------------------------------------
    |
    | These options control the default PDF generation settings. They can be
    | overridden on a per-document basis using the fluent API.
    |
    */
    'pdf' => [
        'format' => env('DOKUFY_PDF_FORMAT', 'A4'),
        'orientation' => env('DOKUFY_PDF_ORIENTATION', 'portrait'),
        'margin_top' => env('DOKUFY_PDF_MARGIN_TOP', '1in'),
        'margin_bottom' => env('DOKUFY_PDF_MARGIN_BOTTOM', '1in'),
        'margin_left' => env('DOKUFY_PDF_MARGIN_LEFT', '0.5in'),
        'margin_right' => env('DOKUFY_PDF_MARGIN_RIGHT', '0.5in'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | This option controls where Dokufy looks for document templates. You may
    | specify the path where your templates are stored.
    |
    */
    'templates' => [
        'path' => env('DOKUFY_TEMPLATES_PATH', resource_path('templates')),
    ],
];
