<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\ConvertPaymentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;

final class ConvertPaymentActionTest extends TestCase
{
    public function testShouldImplements()
    {
        $action = new ConvertPaymentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testShouldCorrectlyConvertPaymentToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setDescription('the description');
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);
        $this->assertArrayHasKey('customer_email', $details);
        $this->assertArrayHasKey('line_items', $details);
        $this->assertIsArray($details['line_items']);
        $this->assertIsArray($details['line_items'][0]);
        $this->assertArrayHasKey('name', $details['line_items'][0]);
        $this->assertArrayHasKey('amount', $details['line_items'][0]);
        $this->assertArrayHasKey('currency', $details['line_items'][0]);
        $this->assertArrayHasKey('quantity', $details['line_items'][0]);
        $this->assertArrayHasKey('payment_method_types', $details);

        $this->assertEquals('test@domain.tld', $details['customer_email']);
        $this->assertEquals('the description', $details['line_items'][0]['name']);
        $this->assertEquals(123, $details['line_items'][0]['amount']);
        $this->assertEquals('USD', $details['line_items'][0]['currency']);
        $this->assertEquals(1, $details['line_items'][0]['quantity']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
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
