<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

trait ResourceAwareActionTrait
{
    /** @var string */
    protected $apiResourceClass = '';

    /**
     * {@inheritDoc}
     */
    public function getApiResourceClass(): string
    {
        return $this->apiResourceClass;
    }

    /**
     * {@inheritDoc}
     */
    public function setApiResourceClass(string $apiResourceClass): void
    {
        $this->apiResourceClass = $apiResourceClass;
    }
}
