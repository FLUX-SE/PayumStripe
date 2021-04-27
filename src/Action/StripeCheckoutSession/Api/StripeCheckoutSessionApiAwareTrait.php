<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession\Api;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;

/**
 * @property StripeCheckoutSessionApiInterface $api
 */
trait StripeCheckoutSessionApiAwareTrait
{
    use StripeApiAwareTrait;

    protected function initApiClass(): void
    {
        $this->apiClass = StripeCheckoutSessionApiInterface::class;
    }
}
