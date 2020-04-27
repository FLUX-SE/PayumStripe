<?php

namespace Tests\Prometee\PayumStripe\Action;

use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripe\Action\StatusAction;
use Stripe\Subscription;

final class StatusActionSubscriptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldMarkCapturedIfIsASubscriptionObjectAndStatusActive()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_ACTIVE,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfIsASubscriptionObjectAndStatusIsTrialing()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_TRIALING,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldNotMarkCapturedIfIsASubscriptionObjectAndStatusIsNotAValidStatus()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
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
    public function shouldMarkCanceledIfIsASubscriptionObjectAndStatusIsCanceled()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_CANCELED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkAsCanceledIfIsASubscriptionObjectAndStatusIncomplete()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_INCOMPLETE,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    /**
     * @test
     */
    public function shouldMarkAsCanceledIfIsASubscriptionObjectAndStatusIncompleteExpired()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_INCOMPLETE_EXPIRED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }
}
