<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Wrapper;

use Stripe\Event;

class EventWrapper
{
    /** @var string */
    protected $usedWebhookSecretKey;
    /** @var Event */
    protected $event;

    /**
     * @param string $usedWebhookSecretKey
     * @param Event $event
     */
    public function __construct(string $usedWebhookSecretKey, Event $event)
    {
        $this->usedWebhookSecretKey = $usedWebhookSecretKey;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getUsedWebhookSecretKey(): string
    {
        return $this->usedWebhookSecretKey;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }
}
