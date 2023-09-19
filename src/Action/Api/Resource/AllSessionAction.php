<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class AllSessionAction extends AbstractAllAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->checkout->sessions;
    }

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllSession;
    }
}
