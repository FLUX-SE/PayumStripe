<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use Stripe\Customer;

final class AllCustomerAction extends AbstractAllAction
{
    protected $apiResourceClass = Customer::class;

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllCustomer;
    }
}
