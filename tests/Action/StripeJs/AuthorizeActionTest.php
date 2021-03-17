<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeJs;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Action\StripeJs\AuthorizeAction;
use FluxSE\PayumStripe\Action\StripeJs\CaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

class AuthorizeActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldBeAnInstanceOf()
    {
        $action = new AuthorizeAction();

        $this->assertInstanceOf(AbstractCaptureAction::class, $action);
        $this->assertInstanceOf(CaptureAction::class, $action);
    }

    public function testShouldSupportOnlyAuthorizeAndArrayAccessModel()
    {
        $action = new AuthorizeAction();

        $this->assertTrue($action->supports(new Authorize([])));
        $this->assertFalse($action->supports(new Authorize(null)));
        $this->assertFalse($action->supports(new Capture(null)));
    }

    public function testShouldDoASyncIfPaymentHasId()
    {
        $model = [
            'id' => 'somethingID',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(CaptureAuthorized::class)]
            )
        ;

        $token = new Token();
        $request = new Authorize($token);
        $request->setModel($model);

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function shouldThrowExceptionWhenThereIsNoTokenAvailable()
    {
        $model = [];

        $request = new Authorize($model);

        $action = new AuthorizeAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function executeAuthorizeAction(array $model): void
    {
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setAfterUrl('test/url');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(CreatePaymentIntent::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(RenderStripeJs::class)]
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

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new Authorize($token);
        $request->setModel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(HttpResponse::class);
        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();
        $this->assertArrayHasKey('capture_method', $resultModel);
        $this->assertEquals('manual', $resultModel['capture_method']);
        $this->assertArrayHasKey('metadata', $resultModel);
        $this->assertArrayHasKey('token_hash', $resultModel['metadata']);
        $this->assertEquals($token->getHash(), $resultModel['metadata']['token_hash']);
    }

    public function testShouldRenderAStripeJsTemplate()
    {
        $model = [];

        $this->executeAuthorizeAction($model);
    }
}
