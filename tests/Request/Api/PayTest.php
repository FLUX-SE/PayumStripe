<?php

namespace Tests\FluxSE\PayumStripe\Request\Api;

use FluxSE\PayumStripe\Request\Api\Pay;
use LogicException;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class PayTest extends TestCase
{
    /** @var Pay */
    private $request;

    protected function setUp(): void
    {
        $this->request = new Pay(new PaymentIntent(), '');
    }

    public function testGetSetActionUrl()
    {
        $actionUrl = 'new/url';
        $this->request->setActionUrl($actionUrl);
        $this->assertEquals($actionUrl, $this->request->getActionUrl());
    }

    public function testGetSetPaymentIntent()
    {
        $paymentIntent = new PaymentIntent('test_1');
        $this->request->setPaymentIntent($paymentIntent);
        $this->assertEquals($paymentIntent, $this->request->getPaymentIntent());

        $this->request->setModel(null);
        $this->expectException(LogicException::class);
        $this->request->getPaymentIntent();
    }
}
