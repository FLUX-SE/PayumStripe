<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

trait OptionsAwareTrait
{
    /** @var array */
    protected $options = [];

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
