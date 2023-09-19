<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Action\Api\Resource\AbstractRetrieveAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelPaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CapturePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\ExpireSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveResourceActionInterface;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelPaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSubscription;
use FluxSE\PayumStripe\Request\Api\Resource\CapturePaymentIntent;
use FluxSE\PayumStripe\Request\Api\Resource\ExpireSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Stripe\ApiRequestor;
use Stripe\ApiResource;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\Subscription;
use Tests\FluxSE\PayumStripe\Action\Api\ApiAwareActionTestTrait;
use Tests\FluxSE\PayumStripe\Stripe\StripeApiTestHelper;

final class CustomCallActionTest extends TestCase
{
    use StripeApiTestHelper;
    use ApiAwareActionTestTrait;

    /**
     * @dataProvider requestList
     */
    public function testShouldImplements(array $customCall, string $retrieveActionClass): void
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
     * @param class-string|RetrieveResourceActionInterface $customCallActionClass
     * @param class-string|AbstractCustomCall              $customCallRequestClass
     * @param class-string|ApiResource                     $customCallClass
     */
    public function testShouldCallCustom(
        array $customCall,
        string $customCallActionClass,
        string $customCallRequestClass,
        string $customCallClass
    ): void {
        $id = 'pi_1';

        $apiMock = $this->createApiMock();

        /** @var AbstractRetrieveAction $action */
        $action = new $customCallActionClass();
        $action->setApiClass(KeysAwareInterface::class);
        $action->setApi($apiMock);

        /** @var AbstractCustomCall $request */
        $request = new $customCallRequestClass($id);
        $this->assertTrue($action->supportAlso($request));

        ApiRequestor::setHttpClient($this->clientMock);

        $resourceUrl = $customCallClass::resourceUrl($id);
        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive([
                'get',
                Stripe::$apiBase . $resourceUrl,
                $this->anything(),
                $this->anything(),
                false,
            ], [
                $customCall[0],
                Stripe::$apiBase . sprintf('%s%s', $resourceUrl, $customCall[1]),
                $this->anything(),
                $this->anything(),
                false,
            ])
            ->willReturnOnConsecutiveCalls(
                [
                    json_encode([
                        'object' => $customCallClass::OBJECT_NAME,
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

        $supportAlso = $action->supportAlso($request);
        $this->assertTrue($supportAlso);

        $supports = $action->supports($request);
        $this->assertTrue($supports);

        $action->execute($request);
        $this->assertInstanceOf($customCallClass, $request->getApiResource());
    }

    public function requestList(): array
    {
        return [
            [['post', '/cancel'], CancelPaymentIntentAction::class, CancelPaymentIntent::class, PaymentIntent::class],
            [['post', '/cancel'], CancelSetupIntentAction::class, CancelSetupIntent::class, SetupIntent::class],
            [['post', '/expire'], ExpireSessionAction::class, ExpireSession::class, Session::class],
            [['delete', null], CancelSubscriptionAction::class, CancelSubscription::class, Subscription::class],
            [['post', '/capture'], CapturePaymentIntentAction::class, CapturePaymentIntent::class, PaymentIntent::class],
        ];
    }
}
