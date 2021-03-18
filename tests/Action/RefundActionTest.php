<?php

namespace Tests\FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Action\RefundAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreateRefund;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Refund;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Stripe\Refund as StripeRefund;
use Stripe\SetupIntent;

final class RefundActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new RefundAction();

        $this->assertInstanceOf(GatewayAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(GenericTokenFactoryAwareInterface::class, $action);
    }

    public function testShouldSupportOnlyRefundWithAnArrayAccessModel()
    {
        $action = new RefundAction();

        $this->assertTrue($action->supports(new Refund([])));
        $this->assertFalse($action->supports(new Refund(null)));
        $this->assertFalse($action->supports(new Authorize(null)));
    }

    public function testShouldDoNothingWhenRequiredModelInfoAreNotAvailable()
    {
        $action = new RefundAction();

        $model = [];
        $request = new Refund($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];
        $request = new Refund($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
        ];
        $request = new Refund($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => '',
        ];
        $request = new Refund($model);
        $supports = $action->supports($request);
        $this->assertTrue($supports);
        $action->execute($request);
    }

    public function testShouldThrowAnExceptionWhenNoTokenIsProvided()
    {
        $action = new RefundAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0000',
        ];
        $request = new Refund($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The request token should not be null !');
        $action->execute($request);
    }

    public function testShouldRefundThePaymentIntent()
    {
        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'id' => 'pi_0000',
        ];

        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setTargetUrl('test/url');

        $notifyToken = new Token();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(CreateRefund::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (CreateRefund $request) use ($notifyToken) {
                    /** @var ArrayObject $model */
                    $model = $request->getModel();
                    $this->assertInstanceOf(ArrayObject::class, $model);
                    $this->assertArrayHasKey('metadata', $model);
                    $this->assertArrayHasKey('token_hash', $model['metadata']);
                    $this->assertEquals($notifyToken->getHash(), $model['metadata']['token_hash']);
                    $request->setApiResource(StripeRefund::constructFrom($model->getArrayCopy()));
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

        $action = new RefundAction();

        $action->setGateway($gatewayMock);
        $action->setGenericTokenFactory($genericGatewayFactory);

        $request = new Refund($token);
        $request->setModel($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();

        $this->assertInstanceOf(ArrayObject::class, $resultModel);
        $this->assertArrayHasKey('payment_intent', $resultModel);
        $this->assertEquals('pi_0000', $resultModel['payment_intent']);
        $this->assertArrayHasKey('metadata', $resultModel);
        $data = $resultModel->offsetGet('metadata');
        $this->assertArrayHasKey('token_hash', $data);
        $this->assertNotEquals($token->getHash(), $data['token_hash']);
        $this->assertEquals($notifyToken->getHash(), $data['token_hash']);
    }
}
