<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\StatusAction;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StatusActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new StatusAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $model = [];
        
        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfDetailsHasErrorSet()
    {
        $action = new StatusAction();

        $model = [
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Amount must be at least 50 cents',
                'param' => 'amount',
            ],
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkRefundedIfIsARefundObjectAndStatusSucceeded()
    {
        $action = new StatusAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => Refund::STATUS_SUCCEEDED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isRefunded());
    }

    /**
     * @test
     */
    public function shouldNotMarkRefundedIfIsARefundObjectAndStatusNotSet()
    {
        $action = new StatusAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertFalse($status->isRefunded());
        $this->assertTrue($status->isNew());
    }

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
    public function shouldNotMarkCapturedIfIsAPaymentIntentObjectAndStatusIsNotSucceeded()
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
    public function shouldMarkCanceledIfIsAPaymentIntentObjectAndStatusRequiresPaymentMethod()
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
    public function shouldMarkCanceledIfIsAPaymentIntentObjectAndStatusRequiresConfirmation()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_CONFIRMATION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfIsAPaymentIntentObjectAndStatusRequiresAction()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => PaymentIntent::STATUS_REQUIRES_ACTION,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfIsAPaymentIntentObjectAndStatusIsNotRequiresPaymentMethod()
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
            'status' => 'test',
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertFalse($status->isAuthorized());
        $this->assertTrue($status->isUnknown());
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

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusCouldNotBeGuessed()
    {
        $action = new StatusAction();

        $model = [
            'status' => 'test',
        ];

        $status = new GetHumanStatus($model);
        $status->markPending();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }
}
