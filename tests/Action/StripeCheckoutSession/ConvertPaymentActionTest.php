<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\ConvertPaymentAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;

final class ConvertPaymentActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    public function testShouldImplements(): void
    {
        $action = new ConvertPaymentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
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
        $payment->setClientEmail('test@domain.tld');
        $payment->setDescription('the description');
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);
        $this->assertArrayHasKey('customer_email', $details);
        $this->assertArrayHasKey('line_items', $details);
        $this->assertArrayHasKey('mode', $details);
        $this->assertIsArray($details['line_items']);
        $this->assertIsArray($details['line_items'][0]);
        $this->assertArrayHasKey('price_data', $details['line_items'][0]);
        $this->assertArrayHasKey('quantity', $details['line_items'][0]);
        $this->assertIsArray($details['line_items'][0]['price_data']);
        $this->assertArrayHasKey('unit_amount', $details['line_items'][0]['price_data']);
        $this->assertArrayHasKey('currency', $details['line_items'][0]['price_data']);
        $this->assertArrayHasKey('product_data', $details['line_items'][0]['price_data']);
        $this->assertIsArray($details['line_items'][0]['price_data']['product_data']);
        $this->assertArrayHasKey('name', $details['line_items'][0]['price_data']['product_data']);

        $this->assertArrayNotHasKey('payment_method_types', $details);

        $this->assertEquals('test@domain.tld', $details['customer_email']);
        $this->assertEquals(Session::MODE_PAYMENT, $details['mode']);
        $this->assertEquals(1, $details['line_items'][0]['quantity']);
        $this->assertEquals(123, $details['line_items'][0]['price_data']['unit_amount']);
        $this->assertEquals('USD', $details['line_items'][0]['price_data']['currency']);
        $this->assertEquals('the description', $details['line_items'][0]['price_data']['product_data']['name']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails(): void
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

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    public function testShouldNotOverwriteAlreadySetPaymentMethodTypes(): void
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails([
            'payment_method_types' => ['alipay'],
        ]);

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('payment_method_types', $details);
        $this->assertEquals(['alipay'], $details['payment_method_types']);
    }

    public function testShouldNotOverwriteAlreadySetCustomerEmail(): void
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails([
            'customer_email' => 'foo@example.tld',
        ]);

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('customer_email', $details);
        $this->assertEquals('foo@example.tld', $details['customer_email']);
    }

    public function testShouldNotOverwriteAlreadySetLineItems(): void
    {
        $payment = new Payment();
        $payment->setClientEmail('test@domain.tld');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails([
            'line_items' => [],
        ]);

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('line_items', $details);
        $this->assertEquals([], $details['line_items']);
    }

    public function testPaymentMethodTypes(): void
    {
        $payment = new Payment();
        $payment->setDetails([
            'customer_email' => '',
            'line_items' => [],
        ]);

        $request = new Convert($payment, 'array');

        $action = new ConvertPaymentAction();

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getPaymentMethodTypes')
            ->willReturn([
                'test'
            ])
        ;

        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $details = $request->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('line_items', $details);
        $this->assertEquals([], $details['line_items']);
        $this->assertArrayHasKey('customer_email', $details);
        $this->assertEquals('', $details['customer_email']);
        $this->assertArrayHasKey('payment_method_types', $details);
        $this->assertEquals(['test'], $details['payment_method_types']);
    }

    protected function getApiClass(): string
    {
        return StripeCheckoutSessionApiInterface::class;
    }
}
