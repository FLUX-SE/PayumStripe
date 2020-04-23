<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\CreateCustomer;
use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
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
