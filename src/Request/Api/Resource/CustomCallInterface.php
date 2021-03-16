<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

interface CustomCallInterface
{
    public function getCustomCallParameters(): array;

    public function setCustomCallParameters(array $callParameters): void;

    public function getCustomCallOptions(): array;

    public function setCustomCallOptions(array $callOptions): void;
}
