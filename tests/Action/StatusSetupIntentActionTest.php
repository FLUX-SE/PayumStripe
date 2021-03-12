<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusSetupIntentAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\SetupIntent;

final class StatusSetupIntentActionTest extends TestCase
{
    public function testShouldImplements()
    {
        $action = new StatusSetupIntentAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterfaceAndArrayAccessObject()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
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
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfErrorIsFound()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'error' => 'an error',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfIsASetupIntentObjectAndStatusSucceeded()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_SUCCEEDED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldNotMarkCapturedIfIsASetupIntentObjectAndStatusIsNotAValidStatus()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => 'test',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertFalse($request->isCaptured());
        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCanceledIfIsASetupIntentObjectAndStatusIsCanceled()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_CANCELED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAsCanceledIfIsASetupIntentObjectAndStatusRequiresPaymentMethod()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresConfirmation()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_CONFIRMATION,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresAction()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_ACTION,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPendingIfIsASetupIntentObjectAndStatusIsProcessing()
    {
        $action = new StatusSetupIntentAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_PROCESSING,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }
}
