<?php

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCharge;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInvoice;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentMethod;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePlan;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveProduct;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\ApiOperations\Retrieve;
use Stripe\ApiResource;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Plan;
use Stripe\Product;
use Stripe\SetupIntent;
use Stripe\Subscription;

final class RetrieveTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $retrieveRequestClass)
    {
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('');

        $this->assertInstanceOf(AbstractRetrieve::class, $retrieveRequest);
        $this->assertInstanceOf(RetrieveInterface::class, $retrieveRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $retrieveRequest);
        $this->assertInstanceOf(ResourceAwareInterface::class, $retrieveRequest);
        $this->assertInstanceOf(Generic::class, $retrieveRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testGetId(string $retrieveRequestClass)
    {
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('');
        $this->assertEquals('', $retrieveRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetId(string $retrieveRequestClass)
    {
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('');
        $id = 'retrieve_1';
        $retrieveRequest->setId($id);
        $this->assertEquals($id, $retrieveRequest->getId());
    }

    /**
     * @dataProvider requestList
     */
    public function testOptions(string $retrieveRequestClass)
    {
        $options = ['test' => 'test'];
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('', $options);
        $this->assertEquals($options, $retrieveRequest->getOptions());

        $options = [];
        $retrieveRequest->setOptions($options);
        $this->assertEquals($options, $retrieveRequest->getOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testApiResource(string $retrieveRequestClass, string $retrieveClass)
    {
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('');

        /** @var Retrieve&ApiResource $retrieve */
        $retrieve = new $retrieveClass();
        $retrieveRequest->setApiResource($retrieve);
        $this->assertEquals($retrieve, $retrieveRequest->getApiResource());
    }

    /**
     * @dataProvider requestList
     */
    public function testNullApiResource(string $retrieveRequestClass)
    {
        /** @var AbstractRetrieve $retrieveRequest */
        $retrieveRequest = new $retrieveRequestClass('');
        $this->expectException(LogicException::class);
        $retrieveRequest->getApiResource();
    }

    public function requestList(): array
    {
        return [
            [RetrieveCharge::class, Charge::class],
            [RetrieveCustomer::class, Customer::class],
            [RetrieveInvoice::class, Invoice::class],
            [RetrievePaymentIntent::class, PaymentIntent::class],
            [RetrievePaymentMethod::class, PaymentMethod::class],
            [RetrievePlan::class, Plan::class],
            [RetrieveProduct::class, Product::class],
            [RetrieveSession::class, Session::class],
            [RetrieveSetupIntent::class, SetupIntent::class],
            [RetrieveSubscription::class, Subscription::class],
        ];
    }
}
