<?php

namespace Tests\FluxSE\PayumStripe\Wrapper;

use FluxSE\PayumStripe\Wrapper\EventWrapper;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class EventWrapperTest extends TestCase
{
    public function testShouldImplementEventWrapperInterface(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertInstanceOf(EventWrapperInterface::class, $eventWrapper);
    }

    public function testShouldNotAlterTheGivenEvent(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals($event, $eventWrapper->getEvent());
    }

    public function testShouldNotAlterTheGivenUsedWebhookSecretKey(): void
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals('', $eventWrapper->getUsedWebhookSecretKey());
    }
}
