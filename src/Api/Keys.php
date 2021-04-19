<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

final class Keys implements KeysInterface
{
    /** @var string[] */
    private $webhookSecretKeys;
    /** @var string */
    private $publishable;
    /** @var string */
    private $secret;
    /** @var string[] */
    private $paymentMethodTypes;

    /**
     * @param string[] $webhookSecretKeys
     */
    public function __construct(
        string $publishable,
        string $secret,
        array $webhookSecretKeys = [],
        array $paymentMethodTypes = []
    ) {
        $this->publishable = $publishable;
        $this->secret = $secret;
        $this->webhookSecretKeys = $webhookSecretKeys;
        $this->paymentMethodTypes = $paymentMethodTypes;
    }

    public function getSecretKey(): string
    {
        return $this->secret;
    }

    public function getPublishableKey(): string
    {
        return $this->publishable;
    }

    public function getWebhookSecretKeys(): array
    {
        return $this->webhookSecretKeys;
    }

    public function hasWebhookSecretKey(string $webhookSecretKey): bool
    {
        return in_array($webhookSecretKey, $this->webhookSecretKeys);
    }

    public function addWebhookSecretKey(string $webhookSecretKey): void
    {
        if (!$this->hasWebhookSecretKey($webhookSecretKey)) {
            $this->webhookSecretKeys[] = $webhookSecretKey;
        }
    }

    public function setWebhookSecretKeys(array $webhookSecretKeys): void
    {
        $this->webhookSecretKeys = $webhookSecretKeys;
    }

    public function getPaymentMethodTypes(): array
    {
        return $this->paymentMethodTypes;
    }

    public function hasPaymentMethodType(string $paymentMethodType): bool
    {
        return in_array($paymentMethodType, $this->paymentMethodTypes);
    }

    public function addPaymentMethodType(string $paymentMethodType): void
    {
        if(!$this->hasPaymentMethodType($paymentMethodType)) {
            $this->paymentMethodTypes[] = $paymentMethodType;
        }
    }

    public function setPaymentMethodTypes(array $paymentMethodTypes): void
    {
        $this->paymentMethodTypes = $paymentMethodTypes;
    }
}
