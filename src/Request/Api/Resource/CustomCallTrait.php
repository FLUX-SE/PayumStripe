<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

trait CustomCallTrait
{
    /** @var array */
    private $customCallParameters = [];

    /** @var array */
    private $customCallOptions = [];

    public function getCustomCallParameters(): array
    {
        return $this->customCallParameters;
    }

    public function setCustomCallParameters(array $customCallParameters): void
    {
        $this->customCallParameters = $customCallParameters;
    }

    public function getCustomCallOptions(): array
    {
        return $this->customCallOptions;
    }

    public function setCustomCallOptions(array $customCallOptions): void
    {
        $this->customCallOptions = $customCallOptions;
    }
}
