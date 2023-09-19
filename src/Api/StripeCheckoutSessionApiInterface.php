<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

interface StripeCheckoutSessionApiInterface extends StripeClientAwareInterface, PaymentMethodTypesAwareInterface
{
    public const DEFAULT_PAYMENT_METHOD_TYPES = [];
}
