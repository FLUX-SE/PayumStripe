<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Stripe\Customer;

class CreateCustomerAction extends AbstractCreateAction
{
    /** @var string|Customer */
    protected $apiResourceClass = Customer::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateCustomer;
    }
}
