<?php

namespace Tests\Prometee\PayumStripe\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreatePlan;
use Prometee\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use Prometee\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Stripe\Plan;

final class CreatePlanTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = [];
        $createPlan = new CreatePlan($model);

        $this->assertInstanceOf(AbstractCreate::class, $createPlan);
        $this->assertInstanceOf(CreateInterface::class, $createPlan);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createPlan);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createPlan);
        $this->assertInstanceOf(Generic::class, $createPlan);
    }

    public function testOptions()
    {
        $model = [];
        $createPlan = new CreatePlan($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createPlan->getOptions());
        $createPlan->setOptions([]);
        $this->assertEquals([], $createPlan->getOptions());
    }

    public function testApiResource()
    {
        $model = [];
        $createPlan = new CreatePlan($model);

        $plan = new Plan();
        $createPlan->setApiResource($plan);

        $this->assertEquals($plan, $createPlan->getApiResource());
    }
}
