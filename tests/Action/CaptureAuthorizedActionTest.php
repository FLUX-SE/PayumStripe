<?php

namespace Tests\FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Action\CaptureAuthorizedAction;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class CaptureAuthorizedActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new CaptureAuthorizedAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GenericTokenFactoryAwareInterface::class, $action);
    }

    public function testShouldSupportOnlyCaptureAuthorizedWithAnArrayAccessModel()
    {
        $action = new CaptureAuthorizedAction();

        $this->assertTrue($action->supports(new CaptureAuthorized([])));
        $this->assertFalse($action->supports(new CaptureAuthorized(null)));
        $this->assertFalse($action->supports(new Capture(null)));
        $this->assertFalse($action->supports(new Authorize(null)));
    }

    public function testShouldDoNothingWhenRequiredModelInfoAreNotAvailable()
    {
        $action = new CaptureAuthorizedAction();

        $model = [];
        $request = new CaptureAuthorized($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];
        $request = new CaptureAuthorized($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CAPTURE,
        ];
        $request = new CaptureAuthorized($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);
    }

    public function testShouldThrowAnExceptionWhenNoTokenIsProvided()
    {
        $action = new CaptureAuthorizedAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CAPTURE,
            'id' => 'pi_0000',
        ];
        $request = new CaptureAuthorized($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The request token should not be null !');
        $action->execute($request);
    }

    public function testShouldCaptureThePaymentIntent()
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CAPTURE,
            'id' => 'pi_0000',
        ];

        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setTargetUrl('test/url');

        $notifyToken = new Token();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(UpdatePaymentIntent::class)],
                [$this->isInstanceOf(CapturePaymentIntent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (UpdatePaymentIntent $request) use ($notifyToken, $model) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $parameters = $request->getParameters();
                    $this->assertArrayHasKey('metadata', $parameters);
                    $this->assertArrayHasKey('capture_authorize_token_hash', $parameters['metadata']);
                    $this->assertEquals($notifyToken->getHash(), $parameters['metadata']['capture_authorize_token_hash']);
                    $request->setApiResource(PaymentIntent::constructFrom(array_merge(
                        $model,
                        $parameters
                    )));
                }),
                $this->returnCallback(function (CapturePaymentIntent $request) use ($notifyToken) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $request->setApiResource(PaymentIntent::constructFrom([
                        'id' => $id,
                        'status' => PaymentIntent::STATUS_SUCCEEDED,
                        'metadata' => [
                            'capture_authorize_token_hash' => $notifyToken->getHash(),
                        ],
                    ]));
                })
            )
        ;

        $genericGatewayFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $genericGatewayFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with($token->getGatewayName(), $this->isInstanceOf(IdentityInterface::class))
            ->willReturn($notifyToken)
        ;

        $action = new CaptureAuthorizedAction();

        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new CaptureAuthorized($token);
        $request->setModel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();

        $this->assertInstanceOf(ArrayObject::class, $resultModel);
        $this->assertArrayHasKey('status', $resultModel);
        $this->assertEquals(PaymentIntent::STATUS_SUCCEEDED, $resultModel->offsetGet('status'));
        $this->assertArrayHasKey('metadata', $resultModel);
        $data = $resultModel->offsetGet('metadata');
        $this->assertArrayHasKey('capture_authorize_token_hash', $data);
        $this->assertNotEquals($token->getHash(), $data['capture_authorize_token_hash']);
        $this->assertEquals($notifyToken->getHash(), $data['capture_authorize_token_hash']);
    }
}
