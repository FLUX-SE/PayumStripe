<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllTaxRate;
use Stripe\Service\AbstractService;
use Stripe\StripeClient;

final class AllTaxRateAction extends AbstractAllAction
{
    public function getStripeService(StripeClient $stripeClient): AbstractService
    {
        return $stripeClient->taxRates;
    }

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllTaxRate;
    }
}
