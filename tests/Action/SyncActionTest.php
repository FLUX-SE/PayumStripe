<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\SyncAction;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\RetrievePaymentIntent;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class SyncActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new SyncAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldGetAPaymentIntentFromASessionObject()
    {
        $model = [
            'object' => Session::OBJECT_NAME,
            'payment_intent' => 'test_1',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrievePaymentIntent::class))
            ->will($this->returnCallback(function (RetrievePaymentIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new PaymentIntent());
            }));

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldGetAPaymentIntentFromAPaymentIntentObject()
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'test_1',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(RetrievePaymentIntent::class))
            ->will($this->returnCallback(function (RetrievePaymentIntent $request) {
                $this->assertIsString($request->getModel());
                $request->setApiResource(new PaymentIntent());
            }));

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingWhenObjectIsNotProvided()
    {
        $model = [
            'id' => 'test_1',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingWhenIdIsNotProvided()
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldDoNothingWhenPaymentIntentIdIsNotProvided()
    {
        $model = [
            'object' => Session::OBJECT_NAME,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync($model);
        $action->execute($request);
    }

    /**
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock(): GatewayInterface
    {
        return $this->createMock(GatewayInterface::class);
    }
}
