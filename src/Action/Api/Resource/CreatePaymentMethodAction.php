<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentMethod;
use Stripe\PaymentMethod;

class CreatePaymentMethodAction extends AbstractCreateAction
{
    protected $apiResourceClass = PaymentMethod::class;

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePaymentMethod;
    }
}
