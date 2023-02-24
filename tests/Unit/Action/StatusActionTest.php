<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action;

use FluxSE\PayumStripe\Action\StatusAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

final class StatusActionTest extends TestCase
{
    public function testShouldImplements(): void
    {
        $action = new StatusAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterface(): void
    {
        $action = new StatusAction();

        $support = $action->supports(new GetHumanStatus([]));
        $this->assertTrue($support);

        $support = $action->supports(new GetBinaryStatus([]));
        $this->assertTrue($support);

        $support = $action->supports(new Capture([]));
        $this->assertFalse($support);
    }

    public function testShouldMarkUnknownIfNoTestsIsPassed(): void
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfObjectIsASession(): void
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

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $model = [];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkFailedIfDetailsHasErrorSet(): void
    {
        $action = new StatusAction();

        $model = [
            'object' => PaymentIntent::OBJECT_NAME,
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

    public function testShouldMarkNewIfStatusCouldNotBeGuessed(): void
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

        $this->assertTrue($request->isNew());
    }
}
