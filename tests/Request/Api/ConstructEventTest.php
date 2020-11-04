<?php

namespace Tests\FluxSE\PayumStripe\Request\Api;

use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class ConstructEventTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertInstanceOf(Generic::class, $constructEvent);
    }

    public function testSetWebhookSecretKey()
    {
        $constructEvent = new ConstructEvent('', '', '');

        $constructEvent->setWebhookSecretKey('my_whsec');

        $this->assertEquals('my_whsec', $constructEvent->getWebhookSecretKey());
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

    public function testPayloadIsNotAString()
    {
        $constructEvent = new ConstructEvent('', '', '');
        $constructEvent->setModel(null);
        $this->expectException(LogicException::class);
        $constructEvent->getPayload();
    }

    public function testChangePayload()
    {
        $constructEvent = new ConstructEvent('', '', '');
        $constructEvent->setPayload('payload_test');
        $this->assertEquals('payload_test', $constructEvent->getPayload());
    }
}
