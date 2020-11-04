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

    /**
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
     * {@inheritdoc}
     */
    public function getSecretKey(): string
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishableKey(): string
    {
        return $this->publishable;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebhookSecretKeys(): array
    {
        return $this->webhookSecretKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function hasWebhookSecretKey(string $webhookSecretKey): bool
    {
        return in_array($webhookSecretKey, $this->webhookSecretKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function addWebhookSecretKey(string $webhookSecretKey): void
    {
        if (!$this->hasWebhookSecretKey($webhookSecretKey)) {
            $this->webhookSecretKeys[] = $webhookSecretKey;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setWebhookSecretKeys(array $webhookSecretKeys): void
    {
        $this->webhookSecretKeys = $webhookSecretKeys;
    }
}
