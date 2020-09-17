<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Api;

interface KeysInterface
{
    public function hasWebhookSecretKey(string $webhookSecretKey): bool;

    /**
     * @param string[] $webhookSecretKeys
     */
    public function setWebhookSecretKeys(array $webhookSecretKeys): void;

    /**
     * @return string[]
     */
    public function getWebhookSecretKeys(): array;

    public function addWebhookSecretKey(string $webhookSecretKey): void;

    public function getSecretKey(): string;

    public function getPublishableKey(): string;
}
