<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripeCheckoutSession\Wrapper\EventWrapper;
use Stripe\Event;

class WebhookEventTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->assertInstanceOf(Generic::class, $webhookEvent);
    }

    public function testSetEventWrapper()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $webhookEvent->setEventWrapper($eventWrapper);

        $this->assertEquals($eventWrapper, $webhookEvent->getEventWrapper());
    }

    public function testGetEventWrapper()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $webhookEvent = new WebhookEvent($eventWrapper);

        $this->assertEquals($eventWrapper, $webhookEvent->getEventWrapper());
    }
}
