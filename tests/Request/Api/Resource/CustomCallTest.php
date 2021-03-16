<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Request\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CustomCallInterface;
use FluxSE\PayumStripe\Request\Api\Resource\OptionsAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\ResourceAwareInterface;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Stripe\Subscription;

final class CustomCallTest extends TestCase
{
    /**
     * @dataProvider requestList
     */
    public function testShouldBeInstanceOf(string $customCallRequestClass)
    {
        /** @var AbstractCustomCall $customCallRequest */
        $customCallRequest = new $customCallRequestClass('');

        $this->assertInstanceOf(AbstractRetrieve::class, $customCallRequest);
        $this->assertInstanceOf(AbstractCustomCall::class, $customCallRequest);
        $this->assertInstanceOf(CustomCallInterface::class, $customCallRequest);
        $this->assertInstanceOf(OptionsAwareInterface::class, $customCallRequest);
        $this->assertInstanceOf(ResourceAwareInterface::class, $customCallRequest);
        $this->assertInstanceOf(Generic::class, $customCallRequest);
    }

    /**
     * @dataProvider requestList
     */
    public function testGetCustomCallParameters(string $customCallRequestClass)
    {
        /** @var AbstractCustomCall $customCallRequest */
        $customCallRequest = new $customCallRequestClass('');
        $this->assertEquals([], $customCallRequest->getCustomCallParameters());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetCustomCallParameters(string $customCallRequestClass)
    {
        /** @var AbstractCustomCall $customCallRequest */
        $customCallRequest = new $customCallRequestClass('');
        $customCallParameters = ['test' => 'test'];
        $customCallRequest->setCustomCallParameters($customCallParameters);
        $this->assertEquals($customCallParameters, $customCallRequest->getCustomCallParameters());
    }

    /**
     * @dataProvider requestList
     */
    public function testGetCustomCallOptions(string $customCallRequestClass)
    {
        /** @var AbstractCustomCall $customCallRequest */
        $customCallRequest = new $customCallRequestClass('');
        $this->assertEquals([], $customCallRequest->getCustomCallOptions());
    }

    /**
     * @dataProvider requestList
     */
    public function testSetCustomCallOptions(string $customCallRequestClass)
    {
        /** @var AbstractCustomCall $customCallRequest */
        $customCallRequest = new $customCallRequestClass('');
        $customCallOptions = ['test' => 'test'];
        $customCallRequest->setCustomCallOptions($customCallOptions);
        $this->assertEquals($customCallOptions, $customCallRequest->getCustomCallOptions());
    }

    public function requestList(): array
    {
        return [
            [CancelPaymentIntent::class, PaymentIntent::class],
            [CancelSubscription::class, Subscription::class],
            [CapturePaymentIntent::class, PaymentIntent::class],
        ];
    }
}
