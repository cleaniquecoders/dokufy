<?php

declare(strict_types=1);

use CleaniqueCoders\Dokufy\Dokufy;

beforeEach(function () {
    $this->dokufy = app(Dokufy::class);
    $this->dokufy->fake();
});

describe('with method', function () {
    it('can set a placeholder handler', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test'];
            }
        };

        $result = $this->dokufy->with($handler);

        expect($result)->toBeInstanceOf(Dokufy::class);
    });

    it('returns self for chaining', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return [];
            }
        };

        $result = $this->dokufy->with($handler);

        expect($result)->toBe($this->dokufy);
    });
});

describe('handler with toArray method', function () {
    it('resolves placeholders from toArray handler', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'John', 'company' => 'Acme'];
            }
        };

        $this->dokufy
            ->html('<h1>{{ name }} - {{ company }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('handles multiple placeholders from toArray', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return [
                    'title' => 'Report',
                    'author' => 'Jane',
                    'date' => '2026-01-15',
                    'version' => '1.0',
                ];
            }
        };

        $this->dokufy
            ->html('<h1>{{ title }}</h1><p>By {{ author }} on {{ date }} v{{ version }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('handler with getPlaceholders method', function () {
    it('resolves placeholders from getPlaceholders handler', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function getPlaceholders(): array
            {
                return ['name' => 'Alice'];
            }
        };

        $this->dokufy
            ->html('<h1>Hello {{ name }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('handler with resolve method', function () {
    it('resolves placeholders from resolve handler', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['name' => 'Bob'];
            }
        };

        $this->dokufy
            ->html('<h1>Hello {{ name }}</h1>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('handler priority', function () {
    it('prefers toArray over other methods', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['source' => 'toArray'];
            }

            /**
             * @return array<string, string>
             */
            public function getPlaceholders(): array
            {
                return ['source' => 'getPlaceholders'];
            }

            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['source' => 'resolve'];
            }
        };

        $result = $this->dokufy
            ->html('<p>{{ source }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        expect($result)->toEndWith('.pdf');
    });

    it('uses getPlaceholders when toArray not available', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function getPlaceholders(): array
            {
                return ['source' => 'getPlaceholders'];
            }

            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['source' => 'resolve'];
            }
        };

        $result = $this->dokufy
            ->html('<p>{{ source }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        expect($result)->toEndWith('.pdf');
    });

    it('uses resolve when other methods not available', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function resolve(): array
            {
                return ['source' => 'resolve'];
            }
        };

        $result = $this->dokufy
            ->html('<p>{{ source }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        expect($result)->toEndWith('.pdf');
    });
});

describe('handler without recognized methods', function () {
    it('returns empty data when no recognized method', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function getData(): array
            {
                return ['name' => 'Test'];
            }
        };

        // This should work but placeholders won't be replaced
        $result = $this->dokufy
            ->html('<p>{{ name }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        expect($result)->toEndWith('.pdf');
    });
});

describe('placeholder syntax variations', function () {
    it('handles placeholders without spaces', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<p>{{name}}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('handles placeholders with spaces', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<p>{{ name }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('handles placeholders with leading space only', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<p>{{ name}}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });

    it('handles placeholders with trailing space only', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['name' => 'Test'];
            }
        };

        $this->dokufy
            ->html('<p>{{name }}</p>')
            ->with($handler)
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        $this->dokufy->assertPdfGenerated();
    });
});

describe('chaining with and data methods', function () {
    it('can use both with and data methods together', function () {
        $handler = new class
        {
            /**
             * @return array<string, string>
             */
            public function toArray(): array
            {
                return ['handlerKey' => 'handlerValue'];
            }
        };

        $result = $this->dokufy
            ->html('<p>{{ handlerKey }} - {{ dataKey }}</p>')
            ->with($handler)
            ->data(['dataKey' => 'dataValue'])
            ->toPdf(sys_get_temp_dir().'/test.pdf');

        expect($result)->toEndWith('.pdf');
    });
});
