<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractAllAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllResourceActionInterface;
use FluxSE\PayumStripe\Action\Api\Resource\AllTaxRateAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractAll;
use FluxSE\PayumStripe\Request\Api\Resource\AllCustomer;
use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllTaxRate;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Customer;
use Stripe\Issuing\CardDetails;
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
    public function testShouldImplements(string $allActionClass)
    {
        $action = new $allActionClass();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
        $this->assertInstanceOf(AllResourceActionInterface::class, $action);
    }

    /**
     * @dataProvider requestList
     */
    public function testShouldAllAPaymentIntent(
        string $allActionClass,
        string $allRequestClass,
        string $allClass
    ) {
        $apiMock = $this->createApiMock();

        /** @var AbstractAllAction $action */
        $action = new $allActionClass();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);
        $this->assertEquals($allClass, $action->getApiResourceClass());

        /** @var AbstractAll $request */
        $request = new $allRequestClass();
        $this->assertTrue($action->supportAlso($request));

        $this->stubRequest(
            'get',
            $allClass::classUrl(),
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
                'url' => $allClass::classUrl(),
            ]
        );

        $action->execute($request);
        $this->assertContainsOnlyInstancesOf($allClass, $request->getApiResources());
    }

    public function testShouldThrowExceptionIfApiResourceClassIsNotCreatable()
    {
        $action = new class() extends AbstractAllAction {
            public function supportAlso(AllInterface $request): bool
            {
                return true;
            }
        };

        $action->setApiResourceClass(CardDetails::class);
        $this->assertEquals(CardDetails::class, $action->getApiResourceClass());

        $request = new class() extends AbstractAll {
        };
        $this->assertTrue($action->supportAlso($request));
        $this->expectException(LogicException::class);
        $action->execute($request);
    }

    public function requestList(): array
    {
        return [
            [AllCustomerAction::class, AllCustomer::class, Customer::class],
            [AllTaxRateAction::class, AllTaxRate::class, TaxRate::class],
        ];
    }
}
