<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentMethod;
use Stripe\PaymentMethod;

class RetrievePaymentMethodAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = PaymentMethod::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrievePaymentMethod;
    }
}
