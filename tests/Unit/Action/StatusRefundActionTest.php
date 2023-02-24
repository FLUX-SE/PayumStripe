<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action;

use FluxSE\PayumStripe\Action\StatusRefundAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use Stripe\Refund;

final class StatusRefundActionTest extends TestCase
{
    use GatewayAwareTestTrait;

    public function testShouldImplements(): void
    {
        $action = new StatusRefundAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
    }

    public function testSupportOnlyGetStatusInterfaceAndArrayAccessObject(): void
    {
        $action = new StatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
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

    public function testShouldMarkUnknownIfNoStatusIsFound(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkFailedIfErrorIsFound(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'error' => 'an error',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkFailedIfIsARefundObjectAndStatusFailed(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => Refund::STATUS_FAILED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkCapturedIfIsARefundObjectAndStatusSucceeded(): void
    {
        $action = $this->createStatusRefundAction();

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

    public function testShouldNotMarkCapturedIfIsARefundObjectAndStatusIsNotAValidStatus(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => 'test',
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertFalse($request->isCaptured());
        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCanceledIfIsARefundObjectAndStatusIsCanceled(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => Refund::STATUS_CANCELED,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkPendingIfIsARefundObjectAndStatusIsPending(): void
    {
        $action = $this->createStatusRefundAction();

        $model = [
            'object' => Refund::OBJECT_NAME,
            'status' => Refund::STATUS_PENDING,
        ];

        $request = new GetHumanStatus($model);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    protected function createStatusRefundAction(): StatusRefundAction
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = new StatusRefundAction();
        $action->setGateway($gatewayMock);

        return $action;
    }
}
