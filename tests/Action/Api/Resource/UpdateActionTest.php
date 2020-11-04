<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractUpdateAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSubscriptionAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractUpdate;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSubscription;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Issuing\CardDetails;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class UpdateActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $updateActionClass)
    {
        $action = new $updateActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(UpdateResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     */
    public function testShouldUpdateAPaymentIntent(
        string $updateActionClass,
        string $updateRequestClass,
        string $updateClass
    ) {
        $id = 'pi_1';
        $parameters = [];

        $apiMock = $this->createApiMock();

        /** @var AbstractUpdateAction $action */
        $action = new $updateActionClass();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);
        $this->assertEquals($updateClass, $action->getApiResourceClass());

        /** @var AbstractUpdate $request */
        $request = new $updateRequestClass($id, $parameters);
        $this->assertTrue($action->supportAlso($request));

        $this->stubRequest(
            'post',
            sprintf('%s/%s', $updateClass::classUrl(), $id),
            [],
            null,
            false,
            [
                'object' => $updateClass::OBJECT_NAME,
                'id' => $id,
            ]
        );

        $action->execute($request);
        $this->assertInstanceOf($updateClass, $request->getApiResource());
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable()
    {
        $id = 'test_1';
        $parameters = [];
        $action = new class() extends AbstractUpdateAction {
            public function supportAlso(UpdateInterface $request): bool
            {
                return true;
            }
        };

        $action->setApiResourceClass(CardDetails::class);
        $this->assertEquals(CardDetails::class, $action->getApiResourceClass());

        $request = new class($id, $parameters) extends AbstractUpdate {
        };
        $this->assertTrue($action->supportAlso($request));
        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function requestList(): array
    {
        return [
            [UpdateSubscriptionAction::class, UpdateSubscription::class, Subscription::class],
        ];
    }
}
