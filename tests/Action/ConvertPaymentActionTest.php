<?php

namespace Tests\Prometee\PayumStripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\ConvertPaymentAction;

final class ConvertPaymentActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new ConvertPaymentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertPaymentToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setDescription('the description');
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $action = new ConvertPaymentAction();
        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

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

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
