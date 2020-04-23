<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api\Resource;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;
use Stripe\ApiResource;
use Stripe\Collection;

interface AllInterface extends OptionsAwareInterface, ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    /**
     * @return array
     */
    public function getParameters(): ?array;

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void;

    /**
     * @return ApiResource[]|Collection|null
     */
    public function getApiResources(): ?Collection;

    /**
     * @param ApiResource[]|Collection $apiResources
     */
    public function setApiResources(Collection $apiResources): void;
}
