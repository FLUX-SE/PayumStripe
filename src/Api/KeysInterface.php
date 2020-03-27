<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Api;

interface KeysInterface
{
    /**
     * @param string $webhookSecretKey
     *
     * @return bool
     */
    public function hasWebhookSecretKey(string $webhookSecretKey): bool;

    /**
     * @param string[] $webhookSecretKeys
     */
    public function setWebhookSecretKeys(array $webhookSecretKeys): void;

    /**
     * @return string[]
     */
    public function getWebhookSecretKeys(): array;

    /**
     * @param string $webhookSecretKey
     */
    public function addWebhookSecretKey(string $webhookSecretKey): void;

    /**
     * @return string
     */
    public function getSecretKey(): string;

    /**
     * @return string
     */
    public function getPublishableKey(): string;
}
