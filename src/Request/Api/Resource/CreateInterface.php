<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;
use Stripe\ApiResource;

interface CreateInterface extends OptionsAwareInterface, ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    /**
     * @return ApiResource|null
     */
    public function getApiResource(): ?ApiResource;

    /**
     * @param ApiResource $apiResource
     */
    public function setApiResource(ApiResource $apiResource): void;
}
