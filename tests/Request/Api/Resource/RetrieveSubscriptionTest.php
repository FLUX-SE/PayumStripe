<?php

namespace Tests\Prometee\PayumStripe\Request\Api\Resource;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use Prometee\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use Prometee\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Prometee\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Prometee\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use Stripe\Subscription;

class RetrieveSubscriptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractRetrieveAndRetrieveInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $retrieveSubscription = new RetrieveSubscription('');

        $this->assertInstanceOf(AbstractRetrieve::class, $retrieveSubscription);
        $this->assertInstanceOf(RetrieveInterface::class, $retrieveSubscription);
        $this->assertInstanceOf(OptionsAwareInterface::class, $retrieveSubscription);
        $this->assertInstanceOf(ResourceAwareInterface::class, $retrieveSubscription);
        $this->assertInstanceOf(Generic::class, $retrieveSubscription);
    }

    public function testOptions()
    {
        $retrieveSubscription = new RetrieveSubscription('', ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $retrieveSubscription->getOptions());
        $retrieveSubscription->setOptions([]);
        $this->assertEquals([], $retrieveSubscription->getOptions());
    }

    public function testApiResource()
    {
        $retrieveSubscription = new RetrieveSubscription('');

        $subscription = new Subscription();
        $retrieveSubscription->setApiResource($subscription);

        $this->assertEquals($subscription, $retrieveSubscription->getApiResource());
    }
}
