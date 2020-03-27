<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Request\Api;

use Payum\Core\Request\Generic;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;

class ConstructEvent extends Generic
{
    /** @var null|string */
    private $webhookSecretKey;
    /** @var string */
    private $sigHeader;

    public function __construct(string $payload, string $sigHeader, string $webhookSecretKey = null)
    {
        parent::__construct($payload);
        $this->sigHeader = $sigHeader;
        $this->webhookSecretKey = $webhookSecretKey;
    }

    /**
     * @param string|null $webhookSecretKey
     */
    public function setWebhookSecretKey(?string $webhookSecretKey): void
    {
        $this->webhookSecretKey = $webhookSecretKey;
    }

    /**
     * @return string|null
     */
    public function getWebhookSecretKey(): ?string
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
     * @param EventWrapper $eventWrapper
     */
    public function setEventWrapper(EventWrapper $eventWrapper): void
    {
        parent::setModel($eventWrapper);
    }

    /**
     * @return EventWrapper|null
     */
    public function getEventWrapper(): ?EventWrapper
    {
        return $this->getModel();
    }
}
