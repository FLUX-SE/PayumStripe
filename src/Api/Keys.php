<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Api;

final class Keys implements KeysInterface
{
    /** @var string[] */
    private $webhookSecretKeys;
    /** @var string */
    private $publishable;
    /** @var string */
    private $secret;

    /**
     * @param string $publishable
     * @param string $secret
     * @param string[] $webhookSecretKeys
     */
    public function __construct(
        string $publishable,
        string $secret,
        array $webhookSecretKeys = []
    ) {
        $this->publishable = $publishable;
        $this->secret = $secret;
        $this->webhookSecretKeys = $webhookSecretKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function getSecretKey(): string
    {
        return $this->secret;
    }

    /**
     * {@inheritDoc}
     */
    public function getPublishableKey(): string
    {
        return $this->publishable;
    }

    /**
     * {@inheritDoc}
     */
    public function getWebhookSecretKeys(): array
    {
        return $this->webhookSecretKeys;
    }

    /**
     * {@inheritDoc}
     */
    public function hasWebhookSecretKey(string $webhookSecretKey): bool
    {
        return in_array($webhookSecretKey, $this->webhookSecretKeys);
    }

    /**
     * {@inheritDoc}
     */
    public function addWebhookSecretKey(string $webhookSecretKey): void
    {
        if (!$this->hasWebhookSecretKey($webhookSecretKey)) {
            $this->webhookSecretKeys[] = $webhookSecretKey;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setWebhookSecretKeys(array $webhookSecretKeys): void
    {
        $this->webhookSecretKeys = $webhookSecretKeys;
    }
}
