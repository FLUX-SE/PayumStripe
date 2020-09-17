<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Wrapper;

use Stripe\Event;

class EventWrapper implements EventWrapperInterface
{
    /** @var string */
    protected $usedWebhookSecretKey;
    /** @var Event */
    protected $event;

    public function __construct(string $usedWebhookSecretKey, Event $event)
    {
        $this->usedWebhookSecretKey = $usedWebhookSecretKey;
        $this->event = $event;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedWebhookSecretKey(): string
    {
        return $this->usedWebhookSecretKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent(): Event
    {
        return $this->event;
    }
}
