<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCreate;
use FluxSE\PayumStripe\Request\Api\Resource\CreateCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePlan;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\CreateTaxRate;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\ApiOperations\Create;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Subscription;
use Stripe\TaxRate;

final class CreateTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $createRequestClass)
    {
        $model = [];
        $createRequest = new $createRequestClass($model);

        $this->assertInstanceOf(AbstractCreate::class, $createRequest);
        $this->assertInstanceOf(CreateInterface::class, $createRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $createRequest);
        $this->assertInstanceOf(ResourceAwareInterface::class, $createRequest);
        $this->assertInstanceOf(Generic::class, $createRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testOptions(string $createRequestClass)
    {
        $model = [];
        /** @var AbstractCreate $createRequest */
        $options = ['test' => 'test'];
        $createRequest = new $createRequestClass($model, $options);
        $this->assertEquals($options, $createRequest->getOptions());

        $options = [];
        $createRequest->setOptions($options);
        $this->assertEquals($options, $createRequest->getOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testApiResource(string $createRequestClass, string $createClass)
    {
        $model = [];
        /** @var AbstractCreate $createRequest */
        $createRequest = new $createRequestClass($model);

        /** @var Create&ApiResource $stripeCreate */
        $stripeCreate = new $createClass();
        $createRequest->setApiResource($stripeCreate);

        $this->assertEquals($stripeCreate, $createRequest->getApiResource());
    }

    /**
     * @dataProvider requestList
     */
    public function testNullApiResource(string $createRequestClass)
    {
        $model = [];
        /** @var AbstractCreate $createRequest */
        $createRequest = new $createRequestClass($model);
        $this->expectException(LogicException::class);
        $createRequest->getApiResource();
    }

    /**
     * @dataProvider requestList
     */
    public function testSetParameters(string $createRequestClass)
    {
        $model = [];
        $parameters = ['field' => 'value'];
        /** @var AbstractCreate $createRequest */
        $createRequest = new $createRequestClass($model);
        $createRequest->setParameters($parameters);
        $this->assertEquals($parameters, $createRequest->getModel()->getArrayCopy());
    }

    /**
     * @dataProvider requestList
     */
    public function testGetParameters(string $createRequestClass)
    {
        $model = [];
        $parameters = [];
        /** @var AbstractCreate $createRequest */
        $createRequest = new $createRequestClass($model);
        $this->assertEquals($parameters, $createRequest->getParameters());

        $createRequest->setModel(null);
        $this->expectException(LogicException::class);
        $createRequest->getParameters();
    }

    public function requestList(): array
    {
        return [
            [CreateCustomer::class, Customer::class],
            [CreateSession::class, Session::class],
            [CreatePlan::class, Plan::class],
            [CreateSubscription::class, Subscription::class],
            [CreateTaxRate::class, TaxRate::class],
        ];
    }
}
