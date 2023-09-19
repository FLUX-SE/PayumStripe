<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class CreateCustomerAction extends AbstractCreateAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->customers;
    }

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateCustomer;
    }
}
