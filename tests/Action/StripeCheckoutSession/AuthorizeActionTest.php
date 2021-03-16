<?php

namespace Tests\FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayObject;
use FluxSE\PayumStripe\Action\AbstractCaptureAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\AuthorizeAction;
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
use Tests\FluxSE\PayumStripe\Action\GatewayAwareTestTrait;

final class AuthorizeActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
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

    public function testShouldThrowExceptionWhenThereIsNoTokenAvailable()
    {
        $model = [];

        $request = new Authorize($model);

        $action = new AuthorizeAction();

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
        $objectName = PaymentIntent::OBJECT_NAME;
        $this->assertTrue($resultModel->offsetExists($objectName.'_data'));
        $data = $resultModel->offsetGet($objectName.'_data');
        $this->assertArrayHasKey('capture_method', $data);
        $this->assertEquals('manual', $data['capture_method']);
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

        $this->executeCaptureAction($model);
    }

    public function testShouldThrowExceptionIfPaymentIsNewAndThereIsASetupIntentDataField()
    {
        $model = [
            'setup_intent_data' => [],
        ];

        $request = new Authorize(new Token());
        $request->setModel($model);

        $action = new AuthorizeAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Authorize is reserved to `mode`=`payment` !');
        $action->execute($request);
    }

    public function testShouldThrowExceptionIfPaymentIsNewAndSetupModeIsSet()
    {
        $model = [
            'mode' => 'setup',
        ];

        $request = new Authorize(new Token());
        $request->setModel($model);

        $action = new AuthorizeAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Authorize is reserved to `mode`=`payment` !');
        $action->execute($request);
    }

    public function testShouldThrowExceptionIfPaymentIsNewAndThereIsASubscriptionDataField()
    {
        $model = [
            'subscription_data' => [],
        ];

        $request = new Authorize(new Token());
        $request->setModel($model);

        $action = new AuthorizeAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Authorize is reserved to `mode`=`payment` !');
        $action->execute($request);
    }

    public function testShouldThrowExceptionIfPaymentIsNewAndSubscriptionModeIsSet()
    {
        $model = [
            'mode' => 'subscription',
        ];

        $request = new Authorize(new Token());
        $request->setModel($model);

        $action = new AuthorizeAction();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Authorize is reserved to `mode`=`payment` !');
        $action->execute($request);
    }
}
