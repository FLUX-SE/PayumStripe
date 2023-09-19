<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractUpdateAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateCouponAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdatePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdatePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdatePriceAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateProductAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSubscriptionAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractUpdate;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePlan;
use FluxSE\PayumStripe\Request\Api\Resource\UpdatePrice;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateProduct;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSubscription;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Coupon;
use Stripe\Issuing\CardDetails;
use Stripe\PaymentIntent;
use Stripe\Plan;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\AbstractService;
use Stripe\Service\CouponService;
use Stripe\Service\PaymentIntentService;
use Stripe\Service\PlanService;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\Service\SubscriptionService;
use Stripe\StripeClient;
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
        string $updateClass,
        string $serviceClass
    ): void {
        $id = 'pi_1';
        $parameters = [];

        $apiMock = $this->createApiMock();
        $stripeClient = $apiMock->getStripeClient();

        /** @var AbstractUpdateAction $action */
        $action = new $updateActionClass();
        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

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

        $service = $action->getStripeService($stripeClient);
        $this->assertInstanceOf($serviceClass, $service);
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

            public function getStripeService(StripeClient $stripeClient): AbstractService
            {
                return new class() extends AbstractService {
                };
            }
        };

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
            [UpdateCouponAction::class, UpdateCoupon::class, Coupon::class, CouponService::class],
            [UpdatePaymentIntentAction::class, UpdatePaymentIntent::class, PaymentIntent::class, PaymentIntentService::class],
            [UpdatePlanAction::class, UpdatePlan::class, Plan::class, PlanService::class],
            [UpdatePriceAction::class, UpdatePrice::class, Price::class, PriceService::class],
            [UpdateProductAction::class, UpdateProduct::class, Product::class, ProductService::class],
            [UpdateSubscriptionAction::class, UpdateSubscription::class, Subscription::class, SubscriptionService::class],
        ];
    }
}
