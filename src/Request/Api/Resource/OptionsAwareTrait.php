<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api\Resource;

trait OptionsAwareTrait
{
    /** @var array */
    protected $options = [];

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
