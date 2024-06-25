<?php

namespace Tests\FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Action\CancelAuthorizedAction;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class CancelAuthorizedActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new CancelAuthorizedAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GenericTokenFactoryAwareInterface::class, $action);
    }

    public function testShouldSupportOnlyCancelWithAnArrayAccessModelWithCaptureMethodManual(): void
    {
        $action = new CancelAuthorizedAction();

        $this->assertTrue($action->supports(new Cancel(['object' => PaymentIntent::OBJECT_NAME, 'capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL])));
        $this->assertFalse($action->supports(new Cancel(['object' => PaymentIntent::OBJECT_NAME, 'capture_method' => PaymentIntent::CAPTURE_METHOD_AUTOMATIC])));
        $this->assertFalse($action->supports(new Cancel([])));
        $this->assertFalse($action->supports(new Cancel(null)));
        $this->assertFalse($action->supports(new Authorize(['capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL])));
    }

    public function testShouldDoNothingWhenRequiredModelInfoAreNotAvailable(): void
    {
        $action = new CancelAuthorizedAction();

        $model = ['capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL];
        $request = new Cancel($model);
        $supports = $action->supports($request);
        $this->assertFalse($supports);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL,
        ];
        $request = new Cancel($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => '',
            'capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL,
        ];
        $request = new Cancel($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);
    }

    public function testShouldThrowAnExceptionWhenNoTokenIsProvided(): void
    {
        $action = new CancelAuthorizedAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0000',
            'capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL,
        ];
        $request = new Cancel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The request token should not be null !');
        $action->execute($request);
    }

    public function testShouldCancelThePaymentIntent(): void
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0000',
            'capture_method' => PaymentIntent::CAPTURE_METHOD_MANUAL,
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
                [$this->isInstanceOf(CancelPaymentIntent::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (UpdatePaymentIntent $request) use ($notifyToken) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $parameters = $request->getParameters();
                    $this->assertArrayHasKey('metadata', $parameters);
                    $this->assertArrayHasKey('cancel_authorized_token_hash', $parameters['metadata']);
                    $this->assertEquals($notifyToken->getHash(), $parameters['metadata']['cancel_authorized_token_hash']);
                    $request->setApiResource(PaymentIntent::constructFrom(array_merge(
                        ['id' => $id],
                        $parameters
                    )));
                }),
                $this->returnCallback(function (CancelPaymentIntent $request) use ($notifyToken) {
                    $id = $request->getModel();
                    $this->assertIsString($id);
                    $request->setApiResource(PaymentIntent::constructFrom([
                        'id' => $id,
                        'status' => PaymentIntent::STATUS_CANCELED,
                        'metadata' => [
                            'cancel_authorized_token_hash' => $notifyToken->getHash(),
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

        $action = new CancelAuthorizedAction();

        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new Cancel($token);
        $request->setModel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();

        $this->assertInstanceOf(ArrayObject::class, $resultModel);
        $this->assertArrayHasKey('status', $resultModel);
        $this->assertEquals(PaymentIntent::STATUS_CANCELED, $resultModel->offsetGet('status'));
        $this->assertArrayHasKey('metadata', $resultModel);
        $data = $resultModel->offsetGet('metadata');
        $this->assertArrayHasKey('cancel_authorized_token_hash', $data);
        $this->assertNotEquals($token->getHash(), $data['cancel_authorized_token_hash']);
        $this->assertEquals($notifyToken->getHash(), $data['cancel_authorized_token_hash']);
    }
}
