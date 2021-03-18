<?php

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractCreateAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePaymentMethodAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateRefundAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateTaxRateAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractCreate;
use FluxSE\PayumStripe\Request\Api\Resource\CreateCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePaymentMethod;
use FluxSE\PayumStripe\Request\Api\Resource\CreatePlan;
use FluxSE\PayumStripe\Request\Api\Resource\CreateRefund;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\CreateTaxRate;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Issuing\CardDetails;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Plan;
use Stripe\Refund;
use Stripe\SetupIntent;
use Stripe\Subscription;
use Stripe\TaxRate;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class CreateActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(string $createActionClass)
    {
        $action = new $createActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(CreateResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     */
    public function testShouldCreateACustomer(
        string $createActionClass,
        string $createRequestClass,
        string $createClass
    ) {
        $model = [];

        $apiMock = $this->createApiMock();

        /** @var AbstractCreateAction $action */
        $action = new $createActionClass();
        $action->setApiClass(KeysInterface::class);
        $this->assertEquals($createClass, $action->getApiResourceClass());
        $action->setApi($apiMock);

        /** @var AbstractCreate $request */
        $request = new $createRequestClass($model);
        $this->assertTrue($action->supportAlso($request));

        $this->stubRequest(
            'post',
            $createClass::classUrl(),
            [],
            null,
            false,
            [
                'object' => $createClass::OBJECT_NAME,
                'id' => 'test_id_0',
            ]
        );

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
        $this->assertInstanceOf($createClass, $request->getApiResource());
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable()
    {
        $model = [];
        $action = new class() extends AbstractCreateAction {
            public function supportAlso(CreateInterface $request): bool
            {
                return true;
            }
        };

        $action->setApiResourceClass(CardDetails::class);
        $this->assertEquals(CardDetails::class, $action->getApiResourceClass());

        $request = new class($model) extends AbstractCreate {
        };

        $this->assertTrue($action->supportAlso($request));
        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function requestList(): array
    {
        return [
            [CreateCustomerAction::class, CreateCustomer::class, Customer::class],
            [CreateSessionAction::class, CreateSession::class, Session::class],
            [CreatePaymentIntentAction::class, CreatePaymentIntent::class, PaymentIntent::class],
            [CreatePaymentMethodAction::class, CreatePaymentMethod::class, PaymentMethod::class],
            [CreatePlanAction::class, CreatePlan::class, Plan::class],
            [CreateRefundAction::class, CreateRefund::class, Refund::class],
            [CreateSetupIntentAction::class, CreateSetupIntent::class, SetupIntent::class],
            [CreateSubscriptionAction::class, CreateSubscription::class, Subscription::class],
            [CreateTaxRateAction::class, CreateTaxRate::class, TaxRate::class],
        ];
    }
}
