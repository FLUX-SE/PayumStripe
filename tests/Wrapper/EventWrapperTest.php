<?php

namespace Tests\Prometee\PayumStripe\Wrapper;

use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Wrapper\EventWrapper;
use Prometee\PayumStripe\Wrapper\EventWrapperInterface;
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
