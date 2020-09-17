<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCreate;
use FluxSE\PayumStripe\Request\Api\Resource\CreateCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Customer;

final class CreateCustomerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = [];
        $createCustomer = new CreateCustomer($model);

        $this->assertInstanceOf(AbstractCreate::class, $createCustomer);
        $this->assertInstanceOf(CreateInterface::class, $createCustomer);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createCustomer);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createCustomer);
        $this->assertInstanceOf(Generic::class, $createCustomer);
    }

    public function testOptions()
    {
        $model = [];
        $createCustomer = new CreateCustomer($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createCustomer->getOptions());
        $createCustomer->setOptions([]);
        $this->assertEquals([], $createCustomer->getOptions());
    }

    public function testApiResource()
    {
        $model = [];
        $createCustomer = new CreateCustomer($model);

        $customer = new Customer();
        $createCustomer->setApiResource($customer);

        $this->assertEquals($customer, $createCustomer->getApiResource());
    }
}
