<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class RetrievePaymentIntentTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractRetrieveAndRetrieveInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $retrievePaymentIntent = new RetrievePaymentIntent('');

        $this->assertInstanceOf(AbstractRetrieve::class, $retrievePaymentIntent);
        $this->assertInstanceOf(RetrieveInterface::class, $retrievePaymentIntent);
        $this->assertInstanceOf(OptionsAwareInterface::class, $retrievePaymentIntent);
        $this->assertInstanceOf(ResourceAwareInterface::class, $retrievePaymentIntent);
        $this->assertInstanceOf(Generic::class, $retrievePaymentIntent);
    }

    public function testOptions()
    {
        $retrievePaymentIntent = new RetrievePaymentIntent('', ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $retrievePaymentIntent->getOptions());
        $retrievePaymentIntent->setOptions([]);
        $this->assertEquals([], $retrievePaymentIntent->getOptions());
    }

    public function testApiResource()
    {
        $retrievePaymentIntent = new RetrievePaymentIntent('');

        $paymentIntent = new PaymentIntent();
        $retrievePaymentIntent->setApiResource($paymentIntent);

        $this->assertEquals($paymentIntent, $retrievePaymentIntent->getApiResource());
    }
}
