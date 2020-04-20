<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Stripe\ApiResource;

trait ResourceAwareTrait
{
    /**
     * @var ApiResource|null
     */
    protected $apiResource;

    /**
     * {@inheritDoc}
     */
    public function getApiResource(): ?ApiResource
    {
        return $this->apiResource;
    }

    /**
     * {@inheritDoc}
     */
    public function setApiResource(ApiResource $apiResource): void
    {
        $this->apiResource = $apiResource;
    }
}
