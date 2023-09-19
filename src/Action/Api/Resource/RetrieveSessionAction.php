<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class RetrieveSessionAction extends AbstractRetrieveAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->checkout->sessions;
    }

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveSession;
    }
}
