<?php

namespace Tests\FluxSE\PayumStripe\Unit\Api;

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

    public function test__construct2(): void
    {
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertInstanceOf(StripeCheckoutSessionApiInterface::class, $api);
        $this->assertInstanceOf(PaymentMethodTypesAwareInterface::class, $api);
    }

    public function testHasPaymentMethodType(): void
    {
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertFalse($api->hasPaymentMethodType('card'));
        $this->assertFalse($api->hasPaymentMethodType('ideal'));
    }

    public function testGetPaymentMethodTypes(): void
    {
        $api = new StripeCheckoutSessionApi('', '');

        $this->assertEquals(StripeCheckoutSessionApiInterface::DEFAULT_PAYMENT_METHOD_TYPES, $api->getPaymentMethodTypes());
    }

    public function testSetPaymentMethodTypes(): void
    {
        $api = new StripeCheckoutSessionApi('', '');

        $api->setPaymentMethodTypes(['card']);
        $this->assertEquals(['card'], $api->getPaymentMethodTypes());
    }

    public function testAddPaymentMethodType(): void
    {
        $api = new StripeCheckoutSessionApi('', '');

        $api->addPaymentMethodType('ideal');
        $this->assertEquals(['ideal'], $api->getPaymentMethodTypes());
    }
}
