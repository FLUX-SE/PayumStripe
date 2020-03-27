<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\Resource;

use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateCustomer;
use Stripe\Customer;

class CreateCustomerAction extends AbstractCreateAction
{
    /**
     * {@inheritDoc}
     */
    public function getApiResourceClass(): string
    {
        return Customer::class;
    }

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof CreateCustomer;
    }
}
