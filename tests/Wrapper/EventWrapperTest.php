<?php

namespace Tests\FluxSE\PayumStripe\Wrapper;

use FluxSE\PayumStripe\Wrapper\EventWrapper;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class EventWrapperTest extends TestCase
{
    public function testShouldImplementEventWrapperInterface()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertInstanceOf(EventWrapperInterface::class, $eventWrapper);
    }

    public function testShouldNotAlterTheGivenEvent()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals($event, $eventWrapper->getEvent());
    }

    public function testShouldNotAlterTheGivenUsedWebhookSecretKey()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals('', $eventWrapper->getUsedWebhookSecretKey());
    }
}
