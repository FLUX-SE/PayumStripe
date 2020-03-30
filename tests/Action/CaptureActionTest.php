<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\CaptureAction;
use Prometee\PayumStripeCheckoutSession\Request\Api\RedirectToCheckout;
use Prometee\PayumStripeCheckoutSession\Request\Api\Resource\CreateSession;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class CaptureActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $action = new CaptureAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldDoASyncIfPaymentHasId()
    {
        $model = [
            'id' => 'somethingID',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    /**
     * @test
     */
    public function shouldDoARedirectToStripeSessionIfPaymentIsNew()
    {
        $model = [];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(CreateSession::class))
            ->will($this->returnCallback(function (CreateSession $request) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $request->setApiResource(new Session());
            }));
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
            ->will($this->returnCallback(function (Sync $request) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $request->setModel(new PaymentIntent());
            }));
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(RedirectToCheckout::class));

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(new Token());
        $request->setModel($model);
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
