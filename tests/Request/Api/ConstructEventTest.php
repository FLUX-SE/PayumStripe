<?php

namespace Tests\Prometee\PayumStripe\Request\Api;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Request\Api\ConstructEvent;
use Prometee\PayumStripe\Wrapper\EventWrapper;
use Stripe\Event;

class ConstructEventTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $constructEvent = new ConstructEvent('', '');

        $this->assertInstanceOf(Generic::class, $constructEvent);
    }

    public function testSetWebhookSecretKey()
    {
        $constructEvent = new ConstructEvent('', '', '');

        $constructEvent->setWebhookSecretKey(null);

        $this->assertEquals(null, $constructEvent->getWebhookSecretKey());
    }

    public function testGetWebhookSecretKey()
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertEquals('', $constructEvent->getWebhookSecretKey());
    }

    public function testGetSigHeader()
    {
        $constructEvent = new ConstructEvent('', 'sigHeader', '');

        $this->assertEquals('sigHeader', $constructEvent->getSigHeader());
    }

    public function testSetEventWrapper()
    {
        $constructEvent = new ConstructEvent('', 'sigHeader', '');

        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $constructEvent->setEventWrapper($eventWrapper);

        $this->assertEquals($eventWrapper, $constructEvent->getEventWrapper());
    }

    public function testGetEventWrapper()
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertEquals(null, $constructEvent->getEventWrapper());
    }
}
