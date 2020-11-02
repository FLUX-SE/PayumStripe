<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\Customer;

final class RetrieveCustomerAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Customer::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveCustomer;
    }
}
