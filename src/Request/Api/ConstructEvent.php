<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Request\Api;

use LogicException;
use Payum\Core\Request\Generic;
use Prometee\PayumStripe\Wrapper\EventWrapperInterface;

class ConstructEvent extends Generic
{
    /** @var string */
    private $webhookSecretKey;
    /** @var string */
    private $sigHeader;

    /**
     * @param string $payload
     * @param string $sigHeader
     * @param string $webhookSecretKey
     */
    public function __construct(
        string $payload,
        string $sigHeader,
        string $webhookSecretKey
    ) {
        parent::__construct($payload);
        $this->sigHeader = $sigHeader;
        $this->webhookSecretKey = $webhookSecretKey;
    }

    /**
     * @return string
     */
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

    /**
     * @param string $webhookSecretKey
     */
    public function setWebhookSecretKey(string $webhookSecretKey): void
    {
        $this->webhookSecretKey = $webhookSecretKey;
    }

    /**
     * @return string
     */
    public function getWebhookSecretKey(): string
    {
        return $this->webhookSecretKey;
    }

    /**
     * @return string
     */
    public function getSigHeader(): string
    {
        return $this->sigHeader;
    }

    /**
     * @param EventWrapperInterface|null $eventWrapper
     */
    public function setEventWrapper(?EventWrapperInterface $eventWrapper): void
    {
        parent::setModel($eventWrapper);
    }

    /**
     * @return EventWrapperInterface|null
     */
    public function getEventWrapper(): ?EventWrapperInterface
    {
        if ($this->getModel() instanceof EventWrapperInterface) {
            return $this->getModel();
        }

        return null;
    }
}
