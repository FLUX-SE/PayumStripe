<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusPaymentIntentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class StatusPaymentIntentActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new StatusPaymentIntentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterfaceAndArrayAccessObject()
    {
        $action = new StatusPaymentIntentAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];

        $support = $action->supports(new GetHumanStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetBinaryStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetHumanStatus(''));
        $this->assertFalse($support);

        $support = $action->supports(new Capture($model));
        $this->assertFalse($support);
    }

    public function testShouldMarkUnknownIfNoStatusIsFound()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfErrorIsFound()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'error' => 'an error',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfIsAPaymentIntentObjectAndStatusSucceeded()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_SUCCEEDED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkAuthorizedIfIsAPaymentIntentObjectAndStatusRequiresCapture()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CAPTURE,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isAuthorized());
    }

    public function testShouldNotMarkCapturedIfIsAPaymentIntentObjectAndStatusIsNotAValidStatus()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => 'test',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertFalse($request->isCaptured());
        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCanceledIfIsAPaymentIntentObjectAndStatusIsCanceled()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_CANCELED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAsCanceledIfIsAPaymentIntentObjectAndStatusRequiresPaymentMethod()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresConfirmation()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresAction()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_ACTION,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPendingIfIsAPaymentIntentObjectAndStatusIsProcessing()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_PROCESSING,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    protected function createStatusWithGateway(): StatusPaymentIntentAction
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new StatusPaymentIntentAction();
        $action->setGateway($gatewayMock);

        return $action;
    }
}
