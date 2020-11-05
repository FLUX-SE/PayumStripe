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

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldNotMarkCapturedIfIsASetupIntentObjectAndStatusIsNotAValidStatus()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => 'test',
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkCanceledIfIsASetupIntentObjectAndStatusIsCanceled()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_CANCELED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkAsCanceledIfIsASetupIntentObjectAndStatusRequiresPaymentMethod()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_PAYMENT_METHOD,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresConfirmation()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_CONFIRMATION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresAction()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_REQUIRES_ACTION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkPendingIfIsASetupIntentObjectAndStatusIsProcessing()
    {
        $action = new StatusAction();

        $model = [
            'object' => SetupIntent::OBJECT_NAME,
            'status' => SetupIntent::STATUS_PROCESSING,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isPending());
    }
}
