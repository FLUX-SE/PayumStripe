<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

use Stripe\StripeClient;
use Stripe\Util\ApiVersion;

trait StripeClientAwareTrait
{
    use KeysAwareTrait {
        KeysAwareTrait::__construct as private __keysAwareTraitConstruct;
    }

    /** @var null|StripeClient */
    private $stripeClient = null;

    /** @var string|null */
    private $clientId = null;

    /** @var string|null */
    private $stripeAccount = null;

    /** @var string */
    private $stripeVersion = '';

    /**
     * @param string[] $webhookSecretKeys
     */
    public function __construct(
        string $publishable,
        string $secret,
        array $webhookSecretKeys = [],
        ?string $clientId = null,
        ?string $stripeAccount = null,
        string $stripeVersion = ApiVersion::CURRENT
    ) {
        $this->__keysAwareTraitConstruct($publishable, $secret, $webhookSecretKeys);
        $this->clientId = $clientId;
        $this->stripeAccount = $stripeAccount;
        $this->stripeVersion = $stripeVersion;
    }

    public function getStripeClient(): StripeClient
    {
        if (null === $this->stripeClient) {
            $this->stripeClient = new StripeClient([
                'api_key' => $this->getSecretKey(),
                'client_id' => $this->getClientId(),
                'stripe_account' => $this->getStripeAccount(),
                'stripe_version' => $this->getStripeVersion(),
            ]);
        }

        return $this->stripeClient;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getStripeAccount(): ?string
    {
        return $this->stripeAccount;
    }

    public function getStripeVersion(): string
    {
        return $this->stripeVersion;
    }
}
