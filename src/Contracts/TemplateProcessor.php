<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Contracts;

interface TemplateProcessor
{
    /**
     * Load a template file.
     */
    public function load(string $templatePath): self;

    /**
     * Set placeholder values.
     *
     * @param  array<string, mixed>  $data
     */
    public function setValues(array $data): self;

    /**
     * Set values for table row cloning.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function setTableRows(string $placeholder, array $rows): self;

    /**
     * Save processed template.
     */
    public function save(string $outputPath): string;
}
