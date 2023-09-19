<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

use Stripe\StripeClient;

interface StripeClientAwareInterface extends KeysAwareInterface
{
    public function getStripeClient(): StripeClient;
}
