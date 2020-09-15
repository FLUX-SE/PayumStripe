<?php

namespace Tests\FluxSE\PayumStripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;
use FluxSE\PayumStripe\Action\Api\ConstructEventAction;
use FluxSE\PayumStripe\Request\Api\ConstructEvent;
use Stripe\Exception\SignatureVerificationException;

final class ConstructEventActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplements()
    {
        $action = new ConstructEventAction();

        $this->assertInstanceOf(ActionInterface::class, $action);
        $this->assertNotInstanceOf(ApiAwareInterface::class, $action);
        $this->assertNotInstanceOf(GatewayInterface::class, $action);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenInvalidPayloadIsRequested()
    {
        $payload = '';
        $sigHeader = '';
        $webhookSecretKey = '';

        $action = new ConstructEventAction();

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);
        $this->expectException(SignatureVerificationException::class);
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

        $action = new ConstructEventAction();

        $request = new ConstructEvent($payload, $sigHeader, $webhookSecretKey);
        $action->execute($request);

        $this->assertNotNull($request->getEventWrapper());
        $this->assertNotNull($request->getWebhookSecretKey());
    }
}
