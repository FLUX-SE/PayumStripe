<?php

namespace Tests\FluxSE\PayumStripe\Wrapper;

use FluxSE\PayumStripe\Wrapper\EventWrapper;
use FluxSE\PayumStripe\Wrapper\EventWrapperInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class EventWrapperTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementEventWrapperInterface()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertInstanceOf(EventWrapperInterface::class, $eventWrapper);
    }

    /**
     * @test
     */
    public function shouldNotAlterTheGivenEvent()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals($event, $eventWrapper->getEvent());
    }

    /**
     * @test
     */
    public function shouldNotAlterTheGivenUsedWebhookSecretKey()
    {
        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);

        $this->assertEquals('', $eventWrapper->getUsedWebhookSecretKey());
    }
}
