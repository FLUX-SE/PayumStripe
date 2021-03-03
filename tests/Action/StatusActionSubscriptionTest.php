<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\Subscription;

final class StatusActionSubscriptionTest extends TestCase
{
    public function testShouldMarkCapturedIfIsASubscriptionObjectAndStatusActive()
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

    public function testShouldMarkCapturedIfIsASubscriptionObjectAndStatusIsTrialing()
    {
        $action = new StatusAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_TRIALING,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldNotMarkCapturedIfIsASubscriptionObjectAndStatusIsNotAValidStatus()
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

    public function testShouldMarkCanceledIfIsASubscriptionObjectAndStatusIsCanceled()
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

    public function testShouldMarkAsCanceledIfIsASubscriptionObjectAndStatusIncomplete()
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

    public function testShouldMarkAsCanceledIfIsASubscriptionObjectAndStatusIncompleteExpired()
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
