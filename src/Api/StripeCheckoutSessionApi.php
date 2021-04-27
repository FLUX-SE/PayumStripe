<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

final class StripeCheckoutSessionApi implements StripeCheckoutSessionApiInterface
{
    use KeysAwareTrait {
        KeysAwareTrait::__construct as private __keysAwareTraitConstruct;
    }

    use PaymentMethodTypesAwareTrait {
        PaymentMethodTypesAwareTrait::__construct as private __paymentMethodTypesAwareTraitConstruct;
    }

    /**
     * @param string[] $webhookSecretKeys
     */
    public function __construct(
        string $publishable,
        string $secret,
        array $webhookSecretKeys = [],
        array $paymentMethodTypes = self::DEFAULT_PAYMENT_METHOD_TYPES
    ) {
        $this->__keysAwareTraitConstruct($publishable, $secret, $webhookSecretKeys);
        $this->__paymentMethodTypesAwareTraitConstruct($paymentMethodTypes);
    }
}
