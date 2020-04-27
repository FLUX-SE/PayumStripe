<?php

namespace Tests\Prometee\PayumStripe\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripe\Request\Api\Resource\CreateCustomer;
use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use Prometee\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Stripe\Customer;

final class CreateCustomerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeInstanceClassOfAbstractCreateAndCreateInterfaceAndOptionsAwareInterfaceAndGeneric()
    {
        $model = new ArrayObject([]);
        $createCustomer = new CreateCustomer($model);

        $this->assertInstanceOf(AbstractCreate::class, $createCustomer);
        $this->assertInstanceOf(CreateInterface::class, $createCustomer);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createCustomer);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createCustomer);
        $this->assertInstanceOf(Generic::class, $createCustomer);
    }

    public function testOptions()
    {
        $model = new ArrayObject([]);
        $createCustomer = new CreateCustomer($model, ['test' => 'test']);

        $this->assertEquals(['test' => 'test'], $createCustomer->getOptions());
        $createCustomer->setOptions([]);
        $this->assertEquals([], $createCustomer->getOptions());
    }

    public function testApiResource()
    {
        $model = new ArrayObject([]);
        $createCustomer = new CreateCustomer($model);

        $customer = new Customer();
        $createCustomer->setApiResource($customer);

        $this->assertEquals($customer, $createCustomer->getApiResource());
    }
}
