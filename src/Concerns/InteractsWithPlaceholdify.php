<?php

declare(strict_types=1);

namespace CleaniqueCoders\Dokufy\Concerns;

trait InteractsWithPlaceholdify
{
    /**
     * The placeholder handler instance.
     */
    protected ?object $placeholderHandler = null;

    /**
     * Set a Placeholdify handler for advanced placeholder replacement.
     *
     * @param  object  $handler  The PlaceholderHandler instance from cleaniquecoders/placeholdify
     */
    public function with(object $handler): self
    {
        $this->placeholderHandler = $handler;

        return $this;
    }

    /**
     * Process placeholders in the given content.
     *
     * @param  array<string, mixed>  $data
     */
    protected function processPlaceholders(string $content, array $data): string
    {
        // If a Placeholdify handler is set, use it
        if ($this->placeholderHandler !== null) {
            $data = $this->resolvePlaceholderHandlerData();
        }

        // Simple placeholder replacement using {{ key }} syntax
        foreach ($data as $key => $value) {
            if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                $content = str_replace(
                    ['{{ '.$key.' }}', '{{'.$key.'}}', '{{ '.$key.'}}', '{{'.$key.' }}'],
                    (string) $value,
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Resolve data from the Placeholdify handler.
     *
     * @return array<string, mixed>
     */
    protected function resolvePlaceholderHandlerData(): array
    {
        if ($this->placeholderHandler === null) {
            return [];
        }

        // Check if the handler has a toArray method
        if (method_exists($this->placeholderHandler, 'toArray')) {
            return $this->placeholderHandler->toArray();
        }

        // Check if the handler has a getPlaceholders method
        if (method_exists($this->placeholderHandler, 'getPlaceholders')) {
            return $this->placeholderHandler->getPlaceholders();
        }

        // Check if the handler has a resolve method
        if (method_exists($this->placeholderHandler, 'resolve')) {
            return $this->placeholderHandler->resolve();
        }

        return [];
    }
}
