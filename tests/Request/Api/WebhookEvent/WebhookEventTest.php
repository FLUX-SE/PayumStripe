<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\WebhookEvent;

use FluxSE\PayumStripe\Request\Api\WebhookEvent\WebhookEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class WebhookEventTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->assertInstanceOf(Generic::class, $webhookEvent);
    }

    public function testSetEventWrapper(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $webhookEvent->setEventWrapper($eventWrapper);

        $this->assertEquals($eventWrapper, $webhookEvent->getEventWrapper());
    }

    public function testGetEventWrapper(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->assertEquals($eventWrapper, $webhookEvent->getEventWrapper());
    }
}
