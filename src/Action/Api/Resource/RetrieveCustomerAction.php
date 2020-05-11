<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\RetrieveCustomer;
use Prometee\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\Customer;

final class RetrieveCustomerAction extends AbstractRetrieveAction
{
    /** @var string|Customer */
    protected $apiResourceClass = Customer::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveCustomer;
    }
}
