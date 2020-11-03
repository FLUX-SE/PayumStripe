<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractDeleteAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\DeletePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\DeleteResourceActionInterface;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractDelete;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use FluxSE\PayumStripe\Request\Api\Resource\DeletePlan;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiRequestor;
use Stripe\Issuing\Card;
use Stripe\Issuing\CardDetails;
use Stripe\Plan;
use Stripe\Stripe;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class DeleteActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $deleteActionClass)
    {
        $action = new $deleteActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(DeleteResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     */
    public function testShouldDeleteAPaymentIntent(
        string $deleteActionClass,
        string $deleteRequestClass,
        string $deleteClass
    ) {
        $id = 'pi_1';

        $apiMock = $this->createApiMock();

        /** @var AbstractDeleteAction $action */
        $action = new $deleteActionClass();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);
        $this->assertEquals($deleteClass, $action->getApiResourceClass());

        /** @var AbstractDelete $request */
        $request = new $deleteRequestClass($id);
        $this->assertTrue($action->supportAlso($request));

        ApiRequestor::setHttpClient($this->clientMock);
        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive([
                'get',
                Stripe::$apiBase.sprintf('%s/%s', $deleteClass::classUrl(), $id),
                $this->anything(),
                $this->anything(),
                false,
            ], [
                'delete',
                Stripe::$apiBase.sprintf('%s/%s', $deleteClass::classUrl(), $id),
                $this->anything(),
                $this->anything(),
                false,
            ])
            ->willReturnOnConsecutiveCalls(
                [
                    json_encode([
                        'object' => $deleteClass::OBJECT_NAME,
                        'id' => $id,
                    ]),
                    200,
                    [],
                ],
                [
                    json_encode([]),
                    200,
                    [],
                ]
            )
        ;

        $action->execute($request);
        $this->assertInstanceOf($deleteClass, $request->getApiResource());
    }

    /**
     * @dataProvider faultList
     */
    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable(string $faultClass)
    {
        $id = 'test_1';
        $action = new class() extends AbstractDeleteAction {
            public function supportAlso(DeleteInterface $request): bool
            {
                return true;
            }
        };

        $action->setApiResourceClass($faultClass);
        $this->assertEquals($faultClass, $action->getApiResourceClass());

        $request = new class($id) extends AbstractDelete {
        };

        $this->assertTrue($action->supportAlso($request));
        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    /**
     * @dataProvider faultList
     */
    public function testShouldThrowExceptionIfItsNotACancelableApiResource(string $faultClass)
    {
        $id = 'test_1';
        $action = new CancelSubscriptionAction();

        $action->setApiResourceClass($faultClass);
        $this->assertEquals($faultClass, $action->getApiResourceClass());

        $request = new CancelSubscription($id);

        $this->assertTrue($action->supportAlso($request));
        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function faultList(): array
    {
        return [
            [CardDetails::class],
            [Card::class],
        ];
    }

    public function requestList(): array
    {
        return [
            [DeletePlanAction::class, DeletePlan::class, Plan::class],
            [CancelSubscriptionAction::class, CancelSubscription::class, Subscription::class],
        ];
    }
}
