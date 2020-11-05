<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class StatusActionPaymentIntentTest extends TestCase
{
    public function testShouldMarkCapturedIfIsAPaymentIntentObjectAndStatusSucceeded()
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

    public function testShouldNotMarkCapturedIfIsAPaymentIntentObjectAndStatusIsNotAValidStatus()
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

    public function testShouldMarkCanceledIfIsAPaymentIntentObjectAndStatusIsCanceled()
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

    public function testShouldMarkAsCanceledIfIsAPaymentIntentObjectAndStatusRequiresPaymentMethod()
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

    public function testShouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresConfirmation()
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

    public function testShouldMarkAsNewIfIsAPaymentIntentObjectAndStatusRequiresAction()
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

    public function testShouldMarkPendingIfIsAPaymentIntentObjectAndStatusIsProcessing()
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
