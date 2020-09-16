<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

interface OptionsAwareInterface
{
    /**
     * @param array $options
     */
    public function setOptions(array $options): void;

    /**
     * @return array
     */
    public function getOptions(): array;
}
