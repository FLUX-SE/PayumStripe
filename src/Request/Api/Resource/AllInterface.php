<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;
use Stripe\ApiResource;
use Stripe\Collection;

interface AllInterface extends OptionsAwareInterface, ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    public function getParameters(): ?array;

    public function setParameters(array $parameters): void;

    /**
     * @return ApiResource[]|Collection
     */
    public function getApiResources(): Collection;

    /**
     * @param ApiResource[]|Collection $apiResources
     */
    public function setApiResources(Collection $apiResources): void;
}
