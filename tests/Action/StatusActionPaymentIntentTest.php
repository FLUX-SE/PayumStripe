<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class StatusActionPaymentIntentTest extends TestCase
{
    /**
     * @test
     */
    public function shouldMarkCapturedIfIsAPaymentIntentObjectAndStatusSucceeded()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_SUCCEEDED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldNotMarkCapturedIfIsAPaymentIntentObjectAndStatusIsNotAValidStatus()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
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
    public function shouldMarkCanceledIfIsAPaymentIntentObjectAndStatusIsCanceled()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_CANCELED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkAsCanceledIfIsAPaymentIntentObjectAndStatusRequiresPaymentMethod()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresConfirmation()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresAction()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_ACTION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfIsAPaymentIntentObjectAndStatusIsProcessing()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_PROCESSING,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isPending());
    }
}
