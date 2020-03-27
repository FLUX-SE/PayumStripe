<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Payum\Core\Request\Generic;
use Stripe\ApiResource;

abstract class AbstractRetrieve extends Generic implements RetrieveInterface
{
    use OptionsAwareTrait;

    /**
     * @param string $id
     * @param array $options
     */
    public function __construct(string $id, array $options = [])
    {
        parent::__construct($id);
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
