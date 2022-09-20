<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractAll;
use FluxSE\PayumStripe\Request\Api\Resource\AllCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\AllCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllInvoice;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use FluxSE\PayumStripe\Request\Api\Resource\AllTaxRate;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use LogicException;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\TaxRate;

final class AllTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $allRequestClass): void
    {
        /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass();

        $this->assertInstanceOf(AbstractAll::class, $allRequest);
        $this->assertInstanceOf(AllInterface::class, $allRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $allRequest);
        $this->assertInstanceOf(Generic::class, $allRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testOptions(string $allRequestClass): void
    {
        $options = ['test' => 'test'];
        /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass([], $options);
        $this->assertEquals($options, $allRequest->getOptions());

        $options = [];
        $allRequest->setOptions($options);
        $this->assertEquals($options, $allRequest->getOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetParameters(string $allRequestClass): void
    {
        $parameters = ['field' => 'value'];
        /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass();
        $allRequest->setParameters($parameters);
        $this->assertEquals($parameters, $allRequest->getModel()->getArrayCopy());
    }

    /**
     * @dataProvider requestList
     */
    public function testGetParameters(string $allRequestClass): void
    {
        $parameters = []; /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass();
        $this->assertEquals($parameters, $allRequest->getParameters());
        $allRequest->setModel(null);
        $this->assertEquals(null, $allRequest->getParameters());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetApiResources(string $allRequestClass): void
    {
        /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass();
        $apiResources = new Collection();
        $allRequest->setApiResources($apiResources);
        $this->assertEquals($apiResources, $allRequest->getApiResources());
    }

    /**
     * @dataProvider requestList
     */
    public function testGetApiResources(string $allRequestClass): void
    {
        /** @var AbstractAll $allRequest */
        $allRequest = new $allRequestClass();

        $this->expectException(LogicException::class);
        $allRequest->getApiResources();
    }

    public function requestList(): array
    {
        return [
            [AllCoupon::class, Coupon::class],
            [AllCustomer::class, Customer::class],
            [AllInvoice::class, Invoice::class],
            [AllTaxRate::class, TaxRate::class],
            [AllSession::class, Session::class],
        ];
    }
}
