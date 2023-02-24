<?php

namespace Tests\FluxSE\PayumStripe\Unit\Request\Api;

use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use FluxSE\PayumStripe\Wrapper\EventWrapper;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

final class ConstructEventTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertInstanceOf(Generic::class, $constructEvent);
    }

    public function testSetWebhookSecretKey(): void
    {
        $constructEvent = new ConstructEvent('', '', '');

        $constructEvent->setWebhookSecretKey('my_whsec');

        $this->assertEquals('my_whsec', $constructEvent->getWebhookSecretKey());
    }

    public function testGetWebhookSecretKey(): void
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertEquals('', $constructEvent->getWebhookSecretKey());
    }

    public function testGetSigHeader(): void
    {
        $constructEvent = new ConstructEvent('', 'sigHeader', '');

        $this->assertEquals('sigHeader', $constructEvent->getSigHeader());
    }

    public function testSetEventWrapper(): void
    {
        $constructEvent = new ConstructEvent('', 'sigHeader', '');

        $event = new Event();
        $eventWrapper = new EventWrapper('', $event);
        $constructEvent->setEventWrapper($eventWrapper);

        $this->assertEquals($eventWrapper, $constructEvent->getEventWrapper());
    }

    public function testGetEventWrapper(): void
    {
        $constructEvent = new ConstructEvent('', '', '');

        $this->assertEquals(null, $constructEvent->getEventWrapper());
    }

    public function testPayloadIsNotAString(): void
    {
        $constructEvent = new ConstructEvent('', '', '');
        $constructEvent->setModel(null);
        $this->expectException(LogicException::class);
        $constructEvent->getPayload();
    }

    public function testChangePayload(): void
    {
        $constructEvent = new ConstructEvent('', '', '');
        $constructEvent->setPayload('payload_test');
        $this->assertEquals('payload_test', $constructEvent->getPayload());
    }
}
