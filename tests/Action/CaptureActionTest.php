<?php

namespace Tests\FluxSE\PayumStripe\Action;

use ArrayObject;
use FluxSE\PayumStripe\Action\CaptureAction;
use FluxSE\PayumStripe\Request\Api\RedirectToCheckout;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;

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

    public function executeCaptureAction(array $model, string $objectName): void
    {
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setGatewayName('stripe_checkout_session');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
        ;
        $gatewayMock
            ->expects($this->exactly(3))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(CreateSession::class)],
                [$this->isInstanceOf(Sync::class)],
                [$this->isInstanceOf(RedirectToCheckout::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (CreateSession $request) {
                    $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                    $request->setApiResource(new Session('sess_0001'));
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

        $this->expectException(HttpResponse::class);
        $action->execute($request);

        /** @var ArrayObject $resultModel */
        $resultModel = $request->getModel();
        $this->assertTrue($resultModel->offsetExists($objectName.'_data'));
        $data = $resultModel->offsetGet($objectName.'_data');
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('token_hash', $data['metadata']);
        $this->assertEquals($token->getHash(), $data['metadata']['token_hash']);

        // Session metadata
        $this->assertTrue($resultModel->offsetExists('metadata'));
        $data = $resultModel->offsetGet('metadata');
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('token_hash', $data['metadata']);
        $this->assertEquals($token->getHash(), $data['metadata']['token_hash']);
    }

    /**
     * @test
     */
    public function shouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsAPaymentIntentDataField()
    {
        $model = [];
        $objectName = PaymentIntent::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    /**
     * @test
     */
    public function shouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsASetupIntentDataField()
    {
        $model = [
            'setup_intent_data' => [],
        ];
        $objectName = SetupIntent::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    /**
     * @test
     */
    public function shouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsASubscriptionDataField()
    {
        $model = [
            'subscription_data' => [],
        ];
        $objectName = Subscription::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }
}
