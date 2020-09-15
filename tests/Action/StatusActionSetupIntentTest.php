<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\SetupIntent;

final class StatusActionSetupIntentTest extends TestCase
{
    /**
     * @test
     */
    public function shouldMarkCapturedIfIsASetupIntentObjectAndStatusSucceeded()
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

    /**
     * @test
     */
    public function shouldNotMarkCapturedIfIsASetupIntentObjectAndStatusIsNotAValidStatus()
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

    /**
     * @test
     */
    public function shouldMarkCanceledIfIsASetupIntentObjectAndStatusIsCanceled()
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

    /**
     * @test
     */
    public function shouldMarkAsCanceledIfIsASetupIntentObjectAndStatusRequiresPaymentMethod()
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

    /**
     * @test
     */
    public function shouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresConfirmation()
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

    /**
     * @test
     */
    public function shouldMarkAsNewIfIsASetupIntentObjectAndStatusRequiresAction()
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

    /**
     * @test
     */
    public function shouldMarkPendingIfIsASetupIntentObjectAndStatusIsProcessing()
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
