<?php

namespace Tests\FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Action\CaptureAction;
use FluxSE\PayumStripe\Action\JsCaptureAction;
use FluxSE\PayumStripe\Request\Api\Pay;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class JsCaptureActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldBeAnInstanceOf()
    {
        $action = new JsCaptureAction();

        $this->assertInstanceOf(CaptureAction::class, $action);
    }

    public function testShouldDoASyncIfPaymentHasId()
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

        $request = new Capture($model);

        $action = new JsCaptureAction();
        $action->setGateway($gatewayMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function shouldThrowExceptionWhenThereIsNoTokenAvailable()
    {
        $model = [];

        $request = new Capture($model);

        $action = new JsCaptureAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function executeCaptureAction(array $model): void
    {
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setGatewayName('stripe_checkout_session');
        $token->setTargetUrl('test/url');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
        ;
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(CreatePaymentIntent::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(Pay::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (CreatePaymentIntent $request) {
                    $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                    $request->setApiResource(new PaymentIntent('pi_0001'));
                }),
                $this->returnCallback(function (Sync $request) {
                    $model = $request->getModel();
                    $this->assertInstanceOf(ArrayObject::class, $model);
                    $model->exchangeArray([]);
                }),
                $this->throwException(new HttpResponse(''))
            )
        ;

        $genericGatewayFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $genericGatewayFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with($token->getGatewayName(), $this->isInstanceOf(IdentityInterface::class))
            ->willReturn(new Token())
        ;

        $action = new JsCaptureAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new Capture($token);
        $request->setModel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();
        $this->assertArrayHasKey('metadata', $resultModel);
        $this->assertArrayHasKey('token_hash', $resultModel['metadata']);
        $this->assertEquals($token->getHash(), $resultModel['metadata']['token_hash']);
    }

    public function testShouldRenderPayTemplatePaymentIsNew()
    {
        $model = [];

        $this->executeCaptureAction($model);
    }
}
