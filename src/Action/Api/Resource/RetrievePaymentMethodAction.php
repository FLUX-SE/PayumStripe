<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentMethod;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class RetrievePaymentMethodAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->paymentMethods;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrievePaymentMethod;
    }
}
