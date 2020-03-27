<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Api;

use Payum\Stripe\Keys as BaseKeys;

class Keys extends BaseKeys
{
    /** @var string[] */
    protected $webhookSecretKeys;

    /**
     * @param $publishable
     * @param $secret
     * @param string[] $webhookSecretKeys
     */
    public function __construct($publishable, $secret, array $webhookSecretKeys = [])
    {
        $this->webhookSecretKeys = $webhookSecretKeys;
        parent::__construct($publishable, $secret);
    }

    /**
     * @return string[]
     */
    public function getWebhookSecretKeys(): array
    {
        return $this->webhookSecretKeys;
    }

    /**
     * @param string $webhookSecretKey
     *
     * @return bool
     */
    public function hasWebhookSecretKey(string $webhookSecretKey): bool
    {
        return in_array($webhookSecretKey, $this->webhookSecretKeys);
    }

    /**
     * @param string $webhookSecretKey
     */
    public function addWebhookSecretKey(string $webhookSecretKey): void
    {
        if (!$this->hasWebhookSecretKey($webhookSecretKey)) {
            $this->webhookSecretKeys[] = $webhookSecretKey;
        }
    }

    /**
     * @param string[] $webhookSecretKeys
     */
    public function setWebhookSecretKeys(array $webhookSecretKeys): void
    {
        $this->webhookSecretKeys = $webhookSecretKeys;
    }
}
