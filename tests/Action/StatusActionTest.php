<?php

namespace Tests\Prometee\PayumStripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\StatusAction;
use Stripe\Refund;

final class StatusActionTest extends TestCase
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
