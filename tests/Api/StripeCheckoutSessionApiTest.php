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
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertInstanceOf(StripeCheckoutSessionApiInterface::class, $api);
        $this->assertInstanceOf(PaymentMethodTypesAwareInterface::class, $api);
    }

    public function testHasPaymentMethodType()
    {
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertFalse($api->hasPaymentMethodType('card'));
        $this->assertFalse($api->hasPaymentMethodType('ideal'));

        $api->addPaymentMethodType('card');
        $this->assertTrue($api->hasPaymentMethodType('card'));
    }

    public function testGetPaymentMethodTypes()
    {
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertEquals(StripeCheckoutSessionApi::DEFAULT_PAYMENT_METHOD_TYPES, $api->getPaymentMethodTypes());
    }

    public function testSetPaymentMethodTypes()
    {
        $api = new StripeCheckoutSessionApi('', '');

        $api->setPaymentMethodTypes(['card']);
        $this->assertEquals(['card'], $api->getPaymentMethodTypes());
    }

    public function testAddPaymentMethodType()
    {
        $api = new StripeCheckoutSessionApi('', '');

        $api->addPaymentMethodType('ideal');
        $this->assertContains('ideal', $api->getPaymentMethodTypes());
    }
}
