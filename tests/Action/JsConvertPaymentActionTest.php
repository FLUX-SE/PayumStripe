<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\JsConvertPaymentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;

final class JsConvertPaymentActionTest extends TestCase
{
    public function testShouldImplements()
    {
        $action = new JsConvertPaymentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testShouldCorrectlyConvertPaymentToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $request = new Convert($payment, 'array');

        $action = new JsConvertPaymentAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);
        $this->assertArrayHasKey('amount', $details);
        $this->assertArrayHasKey('currency', $details);

        $this->assertEquals(123, $details['amount']);
        $this->assertEquals('USD', $details['currency']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDetails([
            'foo' => 'fooVal',
        ]);

        $request = new Convert($payment, 'array');

        $action = new JsConvertPaymentAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
