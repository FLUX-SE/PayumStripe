<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api;

use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use LogicException;
use Payum\Core\Request\Generic;

class ConstructEvent extends Generic
{
    /** @var string */
    private $webhookSecretKey;
    /** @var string */
    private $sigHeader;

    public function __construct(
        string $payload,
        string $sigHeader,
        string $webhookSecretKey
    ) {
        parent::__construct($payload);
        $this->sigHeader = $sigHeader;
        $this->webhookSecretKey = $webhookSecretKey;
    }

    public function getPayload(): string
    {
        if (is_string($this->getModel())) {
            return (string) $this->getModel();
        }

        throw new LogicException('The payload is not a string !');
    }

    public function setPayload(string $payload): void
    {
        $this->setModel($payload);
    }

    public function setWebhookSecretKey(string $webhookSecretKey): void
    {
        $this->webhookSecretKey = $webhookSecretKey;
    }

    public function getWebhookSecretKey(): string
    {
        return $this->webhookSecretKey;
    }

    public function getSigHeader(): string
    {
        return $this->sigHeader;
    }

    public function setEventWrapper(?EventWrapperInterface $eventWrapper): void
    {
        parent::setModel($eventWrapper);
    }

    public function getEventWrapper(): ?EventWrapperInterface
    {
        if ($this->getModel() instanceof EventWrapperInterface) {
            return $this->getModel();
        }

        return null;
    }
}
