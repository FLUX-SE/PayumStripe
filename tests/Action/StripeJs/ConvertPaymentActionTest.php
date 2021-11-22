<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeJs;

use FluxSE\PayumStripe\Action\StripeJs\ConvertPaymentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;

final class ConvertPaymentActionTest extends TestCase
{
    public function testShouldImplements(): void
    {
        $action = new ConvertPaymentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupports(): void
    {
        $action = new ConvertPaymentAction();

        $this->assertFalse($action->supports(new Capture([])));
        $this->assertFalse($action->supports(new Convert(null, 'string')));
        $this->assertFalse($action->supports(new Convert([], 'array')));
        $this->assertTrue($action->supports(new Convert(new Payment(), 'array')));
    }

    public function testShouldCorrectlyConvertPaymentToDetailsAndSetItBack(): void
    {
        $payment = new Payment();
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

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

    public function testShouldNotOverwriteAlreadySetExtraDetails(): void
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDetails([
            'foo' => 'fooVal',
        ]);

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
