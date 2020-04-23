<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Wrapper;

use Stripe\Event;

class EventWrapper implements EventWrapperInterface
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
     * {@inheritDoc}
     */
    public function getUsedWebhookSecretKey(): string
    {
        return $this->usedWebhookSecretKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent(): Event
    {
        return $this->event;
    }
}
