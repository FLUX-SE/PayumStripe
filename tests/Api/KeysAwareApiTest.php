<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Api;

use FluxSE\PayumStripe\Api\KeysAwareInterface;
use ReflectionClass;

trait KeysAwareApiTest
{
    abstract protected function getApiClass(): string;

    public function test__construct()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', '');

        $this->assertInstanceOf(KeysAwareInterface::class, $keys);
    }

    public function testHasWebhookSecretKey()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', '', ['webhookKey1']);

        $this->assertTrue($keys->hasWebhookSecretKey('webhookKey1'));
        $this->assertFalse($keys->hasWebhookSecretKey('webhookKey2'));
    }

    public function testGetWebhookSecretKeys()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', '', ['webhookKey1']);

        $this->assertEquals(['webhookKey1'], $keys->getWebhookSecretKeys());
    }

    public function testSetWebhookSecretKeys()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', '', ['webhookKey1']);
        $keys->setWebhookSecretKeys([]);
        $this->assertEquals([], $keys->getWebhookSecretKeys());
    }

    public function testGetSecretKey()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', 'secretKey');
        $this->assertEquals('secretKey', $keys->getSecretKey());
    }

    public function testGetPublishableKey()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('publishableKey', '');
        $this->assertEquals('publishableKey', $keys->getPublishableKey());
    }

    public function testAddWebhookSecretKey()
    {
        $keys = (new ReflectionClass($this->getApiClass()))->newInstance('', '', []);
        $keys->addWebhookSecretKey('webhookKeyAdded');
        $this->assertEquals(['webhookKeyAdded'], $keys->getWebhookSecretKeys());
    }
}
