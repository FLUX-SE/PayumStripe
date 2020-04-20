<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Stripe\ApiResource;

interface ResourceAwareInterface
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
