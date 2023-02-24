<?php

namespace Tests\FluxSE\PayumStripe\Unit\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractUpdateAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateCouponAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdatePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSubscriptionAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractUpdate;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSession;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSubscription;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Issuing\CardDetails;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Unit\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Unit\Stripe\StripeApiTestHelper;

final class UpdateActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $updateActionClass): void
    {
        $action = new $updateActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(UpdateResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     *
     * @param class-string|ApiResource $updateClass
     */
    public function testShouldUpdateAPaymentIntent(
        string $updateActionClass,
        string $updateRequestClass,
        string $updateClass
    ): void {
        $id = 'pi_1';
        $parameters = [];

        $apiMock = $this->createApiMock();

        /** @var AbstractUpdateAction $action */
        $action = new $updateActionClass();
        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);
        $this->assertEquals($updateClass, $action->getApiResourceClass());

        /** @var AbstractUpdate $request */
        $request = new $updateRequestClass($id, $parameters);
        $this->assertTrue($action->supportAlso($request));

        $this->stubRequest(
            'post',
            $updateClass::resourceUrl($id),
            [],
            null,
            false,
            [
                'object' => $updateClass::OBJECT_NAME,
                'id' => $id,
            ]
        );

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
        $this->assertInstanceOf($updateClass, $request->getApiResource());
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable(): void
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

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function requestList(): array
    {
        return [
            [UpdateCouponAction::class, UpdateCoupon::class, Coupon::class],
            [UpdatePaymentIntentAction::class, UpdatePaymentIntent::class, PaymentIntent::class],
            [UpdateSetupIntentAction::class, UpdateSetupIntent::class, SetupIntent::class],
            [UpdateSessionAction::class, UpdateSession::class, Session::class],
            [UpdateSubscriptionAction::class, UpdateSubscription::class, Subscription::class],
        ];
    }
}
