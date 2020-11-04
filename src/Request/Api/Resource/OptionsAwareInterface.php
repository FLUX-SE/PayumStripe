<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

interface OptionsAwareInterface
{
    public function setOptions(array $options): void;

    public function getOptions(): array;
}
