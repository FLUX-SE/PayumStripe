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
use Payum\Core\Request\Sync;
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

    public function testShouldSupportOnlyCaptureAuthorizedAndArrayAccessModel()
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
        $token->setGatewayName('stripe_checkout_session');
        $token->setTargetUrl('test/url');

        $notifyTokenHash = '';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
        ;
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(UpdatePaymentIntent::class)],
                [$this->isInstanceOf(CapturePaymentIntent::class)],
                [$this->isInstanceOf(Sync::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (UpdatePaymentIntent $request) use ($token, $notifyTokenHash) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $parameters = $request->getParameters();
                    $this->assertArrayHasKey('metadata', $parameters);
                    $this->assertArrayHasKey('token_hash', $parameters['metadata']);
                    $this->assertNotEquals($token->getHash(), $parameters['metadata']['token_hash']);
                    $notifyTokenHash = $parameters['metadata']['token_hash'];
                    $request->setApiResource(PaymentIntent::constructFrom(array_merge(
                        ['id' => $id],
                        $parameters
                    )));
                }),
                $this->returnCallback(function (CapturePaymentIntent $request) use ($notifyTokenHash) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $request->setApiResource(PaymentIntent::constructFrom([
                        'id' => $id,
                        'status' => PaymentIntent::STATUS_SUCCEEDED,
                        'metadata' => [
                            'token_hash' => $notifyTokenHash,
                        ],
                    ]));
                }),
                $this->returnCallback(function (Sync $request) {
                    $model = $request->getModel();
                    $this->assertInstanceOf(ArrayObject::class, $model);
                })
            )
        ;

        $genericGatewayFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $genericGatewayFactory
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with($token->getGatewayName(), $this->isInstanceOf(IdentityInterface::class))
            ->willReturn(new Token())
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
        $this->assertArrayHasKey('token_hash', $data);
        $this->assertNotEquals($token->getHash(), $data['token_hash']);
        $this->assertEquals($notifyTokenHash, $data['token_hash']);
    }
}
