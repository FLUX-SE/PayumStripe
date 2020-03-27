<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Request\Api\Resource;

use ArrayObject;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\AbstractCreate;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateCustomer;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\OptionsAwareInterface;
use Stripe\Customer;

class CreateCustomerTest extends TestCase
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
