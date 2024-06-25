<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractRetrieveAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveChargeAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveCouponAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveInvoiceAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentMethodAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePriceAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveProductAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSubscriptionAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractRetrieve;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCharge;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInvoice;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePaymentMethod;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePlan;
use FluxSE\PayumStripe\Request\Api\Resource\RetrievePrice;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveProduct;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSubscription;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Plan;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\AbstractService;
use Stripe\Service\ChargeService;
use Stripe\Service\Checkout\SessionService;
use Stripe\Service\CouponService;
use Stripe\Service\CustomerService;
use Stripe\Service\InvoiceService;
use Stripe\Service\PaymentIntentService;
use Stripe\Service\PaymentMethodService;
use Stripe\Service\PlanService;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\Service\SetupIntentService;
use Stripe\Service\SubscriptionService;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class RetrieveActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $retrieveActionClass): void
    {
        $action = new $retrieveActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(RetrieveResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     *
     * @param class-string|ApiResource $retrieveClass
     */
    public function testShouldBeRetrieved(
        string $retrieveActionClass,
        string $retrieveRequestClass,
        string $retrieveClass,
        string $serviceClass
    ): void {
        $id = 'pi_1';

        $apiMock = $this->createApiMock();
        $stripeClient = $apiMock->getStripeClient();

        /** @var AbstractRetrieveAction $action */
        $action = new $retrieveActionClass();
        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        /** @var AbstractRetrieve $request */
        $request = new $retrieveRequestClass($id);
        $this->assertTrue($action->supportAlso($request));

        $this->stubRequest(
            'get',
            $retrieveClass::resourceUrl($id),
            [],
            null,
            false,
            [
                'object' => $retrieveClass::OBJECT_NAME,
                'id' => $id,
            ]
        );

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
        $this->assertInstanceOf($retrieveClass, $request->getApiResource());

        $service = $action->getStripeService($stripeClient);
        $this->assertInstanceOf($serviceClass, $service);
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable(): void
    {
        $id = 'test_1';
        $action = new class() extends AbstractRetrieveAction {
            public function supportAlso(RetrieveInterface $request): bool
            {
                return true;
            }

            public function getStripeService(StripeClient $stripeClient): AbstractService
            {
                return new class() extends AbstractService {
                };
            }
        };

        $request = new class($id) extends AbstractRetrieve {
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
            [RetrieveChargeAction::class, RetrieveCharge::class, Charge::class, ChargeService::class],
            [RetrieveCouponAction::class, RetrieveCoupon::class, Coupon::class, CouponService::class],
            [RetrieveCustomerAction::class, RetrieveCustomer::class, Customer::class, CustomerService::class],
            [RetrieveInvoiceAction::class, RetrieveInvoice::class, Invoice::class, InvoiceService::class],
            [RetrievePaymentIntentAction::class, RetrievePaymentIntent::class, PaymentIntent::class, PaymentIntentService::class],
            [RetrievePaymentMethodAction::class, RetrievePaymentMethod::class, PaymentMethod::class, PaymentMethodService::class],
            [RetrievePlanAction::class, RetrievePlan::class, Plan::class, PlanService::class],
            [RetrievePriceAction::class, RetrievePrice::class, Price::class, PriceService::class],
            [RetrieveProductAction::class, RetrieveProduct::class, Product::class, ProductService::class],
            [RetrieveSessionAction::class, RetrieveSession::class, Session::class, SessionService::class],
            [RetrieveSetupIntentAction::class, RetrieveSetupIntent::class, SetupIntent::class, SetupIntentService::class],
            [RetrieveSubscriptionAction::class, RetrieveSubscription::class, Subscription::class, SubscriptionService::class],
        ];
    }
}
