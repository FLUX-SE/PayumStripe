<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Api;

trait KeysAwareApiTrait
{
    public function testHasWebhookSecretKey(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', ['webhookKey1']);

        $this->assertTrue($api->hasWebhookSecretKey('webhookKey1'));
        $this->assertFalse($api->hasWebhookSecretKey('webhookKey2'));
    }

    public function testGetWebhookSecretKeys(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', ['webhookKey1']);

        $this->assertEquals(['webhookKey1'], $api->getWebhookSecretKeys());
    }

    public function testSetWebhookSecretKeys(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', ['webhookKey1']);
        $api->setWebhookSecretKeys([]);
        $this->assertEquals([], $api->getWebhookSecretKeys());
    }

    public function testGetSecretKey(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', 'secretKey');
        $this->assertEquals('secretKey', $api->getSecretKey());
    }

    public function testGetPublishableKey(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('publishableKey', '');
        $this->assertEquals('publishableKey', $api->getPublishableKey());
    }

    public function testAddWebhookSecretKey(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', []);
        $api->addWebhookSecretKey('webhookKeyAdded');
        $this->assertEquals(['webhookKeyAdded'], $api->getWebhookSecretKeys());
    }
}
