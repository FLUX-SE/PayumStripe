<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractAllAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllCouponAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllInvoiceAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllPlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllPriceAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllProductAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\AllSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllTaxRateAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractAll;
use FluxSE\PayumStripe\Request\Api\Resource\AllCoupon;
use FluxSE\PayumStripe\Request\Api\Resource\AllCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllInvoice;
use FluxSE\PayumStripe\Request\Api\Resource\AllPlan;
use FluxSE\PayumStripe\Request\Api\Resource\AllPrice;
use FluxSE\PayumStripe\Request\Api\Resource\AllProduct;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use FluxSE\PayumStripe\Request\Api\Resource\AllTaxRate;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\Plan;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\AbstractService;
use Stripe\Service\Checkout\SessionService;
use Stripe\Service\CouponService;
use Stripe\Service\CustomerService;
use Stripe\Service\InvoiceService;
use Stripe\Service\PlanService;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\Service\TaxRateService;
use Stripe\StripeClient;
use Stripe\TaxRate;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class AllActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $allActionClass): void
    {
        $action = new $allActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(AllResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     *
     * @param class-string|ApiResource $allClass
     */
    public function testShouldAllAPaymentIntent(
        string $allActionClass,
        string $allRequestClass,
        string $allClass,
        string $serviceClass
    ): void {
        $apiMock = $this->createApiMock();
        $stripeClient = $apiMock->getStripeClient();

        /** @var AbstractAllAction $action */
        $action = new $allActionClass();
        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        /** @var AbstractAll $request */
        $request = new $allRequestClass();
        $this->assertTrue($action->supportAlso($request));

        $classUrl = $allClass::classUrl();
        $this->stubRequest(
            'get',
            $classUrl,
            [],
            null,
            false,
            [
                'object' => 'list',
                'data' => [
                    [
                        'object' => $allClass::OBJECT_NAME,
                        'id' => 'test_id_1',
                    ],
                    [
                        'object' => $allClass::OBJECT_NAME,
                        'id' => 'test_id_2',
                    ],
                ],
                'has_more' => false,
                'url' => $classUrl,
            ]
        );

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
        $this->assertContainsOnlyInstancesOf($allClass, $request->getApiResources());

        $service = $action->getStripeService($stripeClient);
        $this->assertInstanceOf($serviceClass, $service);
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable(): void
    {
        $action = new class() extends AbstractAllAction {
            public function supportAlso(AllInterface $request): bool
            {
                return true;
            }

            public function getStripeService(StripeClient $stripeClient): AbstractService
            {
                return new class($stripeClient) extends AbstractService {
                };
            }
        };

        $request = new class() extends AbstractAll {
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
            [AllCouponAction::class, AllCoupon::class, Coupon::class, CouponService::class],
            [AllCustomerAction::class, AllCustomer::class, Customer::class, CustomerService::class],
            [AllInvoiceAction::class, AllInvoice::class, Invoice::class, InvoiceService::class],
            [AllPlanAction::class, AllPlan::class, Plan::class, PlanService::class],
            [AllPriceAction::class, AllPrice::class, Price::class, PriceService::class],
            [AllProductAction::class, AllProduct::class, Product::class, ProductService::class],
            [AllTaxRateAction::class, AllTaxRate::class, TaxRate::class, TaxRateService::class],
            [AllSessionAction::class, AllSession::class, Session::class, SessionService::class],
        ];
    }
}
