<?php

namespace Tests\Prometee\PayumStripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\CaptureAction;
use Prometee\PayumStripe\Request\Api\RedirectToCheckout;
use Prometee\PayumStripe\Request\Api\Resource\CreateSession;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

final class CaptureActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new CaptureAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GenericTokenFactoryAwareInterface::class, $action);
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
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setGatewayName('stripe_checkout_session');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(CreateSession::class))
            ->will($this->returnCallback(function (CreateSession $request) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $request->setApiResource(new Session());
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
            ->will($this->returnCallback(function (Sync $request) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $request->setModel(new PaymentIntent());
            }))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(RedirectToCheckout::class))
        ;

        $genericGatewayFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $genericGatewayFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with($token->getGatewayName(), $this->isInstanceOf(IdentityInterface::class))
            ->willReturn(new Token())
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new Capture($token);
        $request->setModel($model);
        $action->execute($request);
    }
}
