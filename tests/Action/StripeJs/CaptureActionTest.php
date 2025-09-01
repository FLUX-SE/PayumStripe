<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeJs;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
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

final class CaptureActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldBeAnInstanceOf(): void
    {
        $action = new CaptureAction();

        $this->assertInstanceOf(AbstractCaptureAction::class, $action);
    }

    public function testShouldSupportOnlyCaptureAndArrayAccessModel(): void
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new Capture([])));
        $this->assertFalse($action->supports(new Capture(null)));
        $this->assertFalse($action->supports(new Authorize(null)));
    }

    public function testShouldDoASyncIfPaymentHasId(): void
    {
        $model = [
            'id' => 'somethingID',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->with($this->callback(function ($arg) {
                static $callCount = 0;
                $callCount++;

                switch ($callCount) {
                    case 1:
                        return $arg instanceof Sync;
                    case 2:
                        return $arg instanceof CaptureAuthorized;
                    default:
                        return false;
                }
            }))
        ;

        $token = new Token();
        $request = new Capture($token);
        $request->setModel($model);

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function shouldThrowExceptionWhenThereIsNoTokenAvailable(): void
    {
        $model = [];

        $request = new Capture($model);

        $action = new CaptureAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function executeCaptureAction(array $model): void
    {
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setAfterUrl('test/url');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->with($this->callback(function ($arg) {
                static $callCount = 0;
                $callCount++;

                switch ($callCount) {
                    case 1:
                        return $arg instanceof CreatePaymentIntent;
                    case 2:
                        return $arg instanceof Sync;
                    case 3:
                        return $arg instanceof RenderStripeJs;
                    default:
                        return false;
                }
            }))
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (CreatePaymentIntent $request) {
                    $this->assertEquals([
                        'idempotency_key' => md5(1),
                    ], $request->getOptions());
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

        $action = new CaptureAction();
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

    public function testShouldRenderAStripeJsTemplate(): void
    {
        $model = [];

        $this->executeCaptureAction($model);
    }
}
