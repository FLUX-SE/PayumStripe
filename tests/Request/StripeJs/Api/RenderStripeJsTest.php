<?php

namespace Tests\FluxSE\PayumStripe\Request\StripeJs\Api;

use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class RenderStripeJsTest extends TestCase
{
    /** @var RenderStripeJs */
    private $request;

    protected function setUp(): void
    {
        $this->request = new RenderStripeJs(new PaymentIntent(), '');
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
        $this->request->setApiResource($paymentIntent);
        $this->assertEquals($paymentIntent, $this->request->getApiResource());

        $this->request->setModel(null);
        $this->assertEquals(null, $this->request->getApiResource());
    }
}
