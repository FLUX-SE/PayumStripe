<?php

namespace Tests\FluxSE\PayumStripe\Api;

use FluxSE\PayumStripe\Api\PaymentMethodTypesAwareInterface;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApi;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;
use PHPUnit\Framework\TestCase;

final class StripeCheckoutSessionApiTest extends TestCase
{
    use KeysAwareApiTest;

    protected function getApiClass(): string
    {
        return StripeCheckoutSessionApi::class;
    }

    public function test__construct2()
    {
        $keys = new StripeCheckoutSessionApi('', '');

        $this->assertInstanceOf(StripeCheckoutSessionApiInterface::class, $keys);
        $this->assertInstanceOf(PaymentMethodTypesAwareInterface::class, $keys);
    }

    public function testHasPaymentMethodType()
    {
        $keys = new StripeCheckoutSessionApi('', '', [], ['card']);

        $this->assertTrue($keys->hasPaymentMethodType('card'));
        $this->assertFalse($keys->hasPaymentMethodType('ideal'));
    }

    public function testGetPaymentMethodTypes()
    {
        $keys = new StripeCheckoutSessionApi('', '', [], ['card']);

        $this->assertEquals(['card'], $keys->getPaymentMethodTypes());
    }

    public function testSetPaymentMethodTypes()
    {
        $keys = new StripeCheckoutSessionApi('', '', [], ['card']);
        $keys->setPaymentMethodTypes([]);
        $this->assertEquals([], $keys->getPaymentMethodTypes());
    }

    public function testAddPaymentMethodType()
    {
        $keys = new StripeCheckoutSessionApi('', '', [], []);
        $keys->addPaymentMethodType('card');
        $this->assertEquals(['card'], $keys->getPaymentMethodTypes());
    }
}
