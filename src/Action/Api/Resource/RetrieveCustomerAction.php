<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class RetrieveCustomerAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->customers;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveCustomer;
    }
}
