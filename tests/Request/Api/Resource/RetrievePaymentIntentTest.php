<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\AbstractRetrieve;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\OptionsAwareInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrieveInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\PaymentIntent;

class RetrievePaymentIntentTest extends TestCase
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
