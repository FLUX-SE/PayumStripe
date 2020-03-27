<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSubscription;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\OptionsAwareInterface;
use Stripe\Subscription;

class CreateSubscriptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = new ArrayObject([]);
        $createSubscription = new CreateSubscription($model);

        $this->assertInstanceOf(AbstractCreate::class, $createSubscription);
        $this->assertInstanceOf(CreateInterface::class, $createSubscription);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createSubscription);
        $this->assertInstanceOf(Generic::class, $createSubscription);
    }

    public function testOptions()
    {
        $model = new ArrayObject([]);
        $createSubscription = new CreateSubscription($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createSubscription->getOptions());
        $createSubscription->setOptions([]);
        $this->assertEquals([], $createSubscription->getOptions());
    }

    public function testApiResource()
    {
        $model = new ArrayObject([]);
        $createSubscription = new CreateSubscription($model);

        $subscription = new Subscription();
        $createSubscription->setApiResource($subscription);

        $this->assertEquals($subscription, $createSubscription->getApiResource());
    }
}
