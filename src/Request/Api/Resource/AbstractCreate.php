<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use Stripe\ApiResource;

abstract class AbstractCreate extends Generic implements CreateInterface
{
    use OptionsAwareTrait;

    /**
     * @param ArrayObject $model
     * @param array $options
     */
    public function __construct(ArrayObject $model, array $options = [])
    {
        parent::__construct($model);
        $this->setOptions($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResource(): ?ApiResource
    {
        if ($this->getModel() instanceof ApiResource) {
            return $this->getModel();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setApiResource(ApiResource $apiResource): void
    {
        $this->setModel($apiResource);
    }
}
