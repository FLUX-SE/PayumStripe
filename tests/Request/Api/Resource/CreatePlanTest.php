<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreatePlan;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\OptionsAwareInterface;
use Stripe\Plan;

class CreatePlanTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = new ArrayObject([]);
        $createPlan = new CreatePlan($model);

        $this->assertInstanceOf(AbstractCreate::class, $createPlan);
        $this->assertInstanceOf(CreateInterface::class, $createPlan);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createPlan);
        $this->assertInstanceOf(Generic::class, $createPlan);
    }

    public function testOptions()
    {
        $model = new ArrayObject([]);
        $createPlan = new CreatePlan($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createPlan->getOptions());
        $createPlan->setOptions([]);
        $this->assertEquals([], $createPlan->getOptions());
    }

    public function testApiResource()
    {
        $model = new ArrayObject([]);
        $createPlan = new CreatePlan($model);

        $plan = new Plan();
        $createPlan->setApiResource($plan);

        $this->assertEquals($plan, $createPlan->getApiResource());
    }
}
