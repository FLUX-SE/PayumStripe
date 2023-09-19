<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

use Stripe\Util\ApiVersion;

final class StripeCheckoutSessionApi implements StripeCheckoutSessionApiInterface
{
    use StripeClientAwareTrait {
        StripeClientAwareTrait::__construct as private __stripeClientAwareTraitConstruct;
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
        ?string $clientId = null,
        ?string $stripeAccount = null,
        string $stripeVersion = ApiVersion::CURRENT,
        array $paymentMethodTypes = self::DEFAULT_PAYMENT_METHOD_TYPES
    ) {
        $this->__stripeClientAwareTraitConstruct(
            $publishable,
            $secret,
            $webhookSecretKeys,
            $clientId,
            $stripeAccount,
            $stripeVersion
        );
        $this->__paymentMethodTypesAwareTraitConstruct($paymentMethodTypes);
    }
}
