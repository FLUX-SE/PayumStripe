<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\CaptureAction;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\CaptureAuthorized;
use FluxSE\PayumStripe\Request\StripeCheckoutSession\Api\RedirectToCheckout;
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
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class CaptureActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new CaptureAction();

        $this->assertInstanceOf(AbstractCaptureAction::class, $action);
    }

    public function testShouldSupportOnlyCaptureAndArrayAccessModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new Capture([])));
        $this->assertFalse($action->supports(new Capture(null)));
        $this->assertFalse($action->supports(new Authorize(null)));
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
        $request = new Capture($token);
        $request->setModel($model);

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
    }

    public function testShouldThrowExceptionWhenThereIsNoTokenAvailable()
    {
        $model = [];

        $request = new Capture($model);

        $action = new CaptureAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function executeCaptureAction(array $model, string $objectName): void
    {
        $token = new Token();
        $token->setDetails(new Identity(1, PaymentInterface::class));
        $token->setTargetUrl('test/url');

        $gatewayMock = $this->createGatewayMock();
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

        $supports = $action->supports($request);
        $this->assertTrue($supports);

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
        $this->assertEquals('test/url', $resultModel->offsetGet('success_url'));
        $this->assertEquals('test/url', $resultModel->offsetGet('cancel_url'));
        $this->assertTrue($resultModel->offsetExists('metadata'));
        $data = $resultModel->offsetGet('metadata');
        $this->assertArrayHasKey('metadata', $data);
        $this->assertArrayHasKey('token_hash', $data['metadata']);
        $this->assertEquals($token->getHash(), $data['metadata']['token_hash']);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsAPaymentIntentDataField()
    {
        $model = [];
        $objectName = PaymentIntent::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsASetupIntentDataField()
    {
        $model = [
            'setup_intent_data' => [],
        ];
        $objectName = SetupIntent::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndSetupModeIsSet()
    {
        $model = [
            'mode' => 'setup',
        ];
        $objectName = SetupIntent::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndThereIsASubscriptionDataField()
    {
        $model = [
            'subscription_data' => [],
        ];
        $objectName = Subscription::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndSubscriptionModeIsSet()
    {
        $model = [
            'mode' => 'subscription',
        ];
        $objectName = Subscription::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndPaymentModeIsSet()
    {
        $model = [
            'mode' => 'payment',
        ];
        $objectName = Subscription::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }

    public function testShouldDoARedirectToStripeSessionIfPaymentIsNewAndPaymentModeIsSetWithMetadata()
    {
        $model = [
            'mode' => 'payment',
            'metadata' => [],
        ];
        $objectName = Subscription::OBJECT_NAME;

        $this->executeCaptureAction($model, $objectName);
    }
}
