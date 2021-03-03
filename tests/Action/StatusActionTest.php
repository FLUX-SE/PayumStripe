<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Refund;

final class StatusActionTest extends TestCase
{
    public function testShouldImplements()
    {
        $action = new StatusAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testShouldMarkFailedIfObjectIsASession()
    {
        $action = new StatusAction();

        $model = [
            'object' => Session::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $model = [];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkFailedIfDetailsHasErrorSet()
    {
        $action = new StatusAction();

        $model = [
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Amount must be at least 50 cents',
                'param' => 'amount',
            ],
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkRefundedIfIsARefundObjectAndStatusSucceeded()
    {
        $action = new StatusAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => Refund::STATUS_SUCCEEDED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldNotMarkRefundedIfIsARefundObjectAndStatusNotSet()
    {
        $action = new StatusAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertFalse($request->isRefunded());
        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkUnknownIfItsNotARefundWithUnknownStatus()
    {
        $action = new StatusAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => 'test',
        ];

        $request = new GetHumanStatus($model);
        $request->markPending();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkUnknownIfStatusCouldNotBeGuessed()
    {
        $action = new StatusAction();

        $model = [
            'status' => 'test',
        ];

        $request = new GetHumanStatus($model);
        $request->markPending();

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
