<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\SetupIntent;

final class StatusActionSetupIntentTest extends TestCase
{
    public function testShouldMarkCapturedIfIsASetupIntentObjectAndStatusSucceeded()
    {
        $action = new StatusAction();

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
        $action = new StatusAction();

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
        $action = new StatusAction();

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
        $action = new StatusAction();

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
        $action = new StatusAction();

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
        $action = new StatusAction();

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
        $action = new StatusAction();

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
