<?php

namespace Tests\FluxSE\PayumStripe\Action;

use FluxSE\PayumStripe\Action\StatusSubscriptionAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Subscription;

final class StatusSubscriptionActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements()
    {
        $action = new StatusSubscriptionAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterfaceAndArrayAccessObject()
    {
        $action = new StatusSubscriptionAction();

        $model = [
            'object' => Subscription::OBJECT_NAME,
        ];

        $support = $action->supports(new GetHumanStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetBinaryStatus($model));
        $this->assertTrue($support);

        $support = $action->supports(new GetHumanStatus(''));
        $this->assertFalse($support);

        $support = $action->supports(new Capture($model));
        $this->assertFalse($support);
    }

    public function testShouldMarkUnknownIfNoStatusIsFound()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Subscription::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfErrorIsFound()
    {
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'error' => 'an error',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfIsASubscriptionObjectAndStatusActive()
    {
        $action = $this->createStatusWithGateway();

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
        $action = $this->createStatusWithGateway();

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
        $action = $this->createStatusWithGateway();

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
        $action = $this->createStatusWithGateway();

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
        $action = $this->createStatusWithGateway();

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
        $action = $this->createStatusWithGateway();

        $model = [
            'object' => Subscription::OBJECT_NAME,
            'status' => Subscription::STATUS_INCOMPLETE_EXPIRED,
        ];

        $status = new GetHumanStatus($model);
        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    protected function createStatusWithGateway(): StatusSubscriptionAction
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = new StatusSubscriptionAction();
        $action->setGateway($gatewayMock);

        return $action;
    }
}
