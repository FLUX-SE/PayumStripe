<?php

namespace Tests\Prometee\PayumStripeCheckoutSession\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\Action\Api\ConstructEventAction;
use Prometee\PayumStripeCheckoutSession\Api\KeysInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\ConstructEvent;

class ConstructEventActionTest extends TestCase
{
    use ApiAwareActionTestTrait;

    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new ConstructEventAction();

        $this->assertInstanceOf(ApiAwareInterface::class, $action);
        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldReturnNullWhenInvalidPayloadIsRequested()
    {
        $payload = '';
        $sigHeader = '';
        $webhookSecretKey = null;

        $apiMock = $this->createApiMock();

        $action = new ConstructEventAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);
        $action->execute($request);

        $this->assertNull($request->getEventWrapper());
        $this->assertNull($request->getWebhookSecretKey());
    }

    /**
     * @test
     */
    public function shouldGetAnEventWrapperWhenValidPayloadIsRequested()
    {
        $now = time();

        $webhookSecretKey = 'whsec_test';

        $payload = file_get_contents(__DIR__ . '/../../Resources/Webhooks/checkout-session-completed.json');

        $signedPayload = sprintf('%s.%s', $now, $payload);
        $signature = hash_hmac('sha256', $signedPayload, $webhookSecretKey);

        $sigHeader = sprintf('t=%s,', $now);
        $sigHeader .= sprintf('v1=%s,', $signature);
        // Useless but here to tests legacy too
        $sigHeader .= 'v0=6ffbb59b2300aae63f272406069a9788598b792a944a07aba816edb039989a39';

        $apiMock = $this->createApiMock();

        $action = new ConstructEventAction();
        $action->setApiClass(KeysInterface::class);
        $action->setApi($apiMock);

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);
        $action->execute($request);

        $this->assertNotNull($request->getEventWrapper());
        $this->assertNotNull($request->getWebhookSecretKey());
    }
}
