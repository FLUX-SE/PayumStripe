<?php

namespace Tests\FluxSE\PayumStripe\Api;

use FluxSE\PayumStripe\Api\Keys;
use FluxSE\PayumStripe\Api\KeysInterface;
use PHPUnit\Framework\TestCase;

final class KeysTest extends TestCase
{
    public function test__construct()
    {
        $keys = new Keys('', '');

        $this->assertInstanceOf(KeysInterface::class, $keys);
    }

    public function testHasWebhookSecretKey()
    {
        $keys = new Keys('', '', ['webhookKey1']);

        $this->assertTrue($keys->hasWebhookSecretKey('webhookKey1'));
        $this->assertFalse($keys->hasWebhookSecretKey('webhookKey2'));
    }

    public function testGetWebhookSecretKeys()
    {
        $keys = new Keys('', '', ['webhookKey1']);

        $this->assertEquals(['webhookKey1'], $keys->getWebhookSecretKeys());
    }

    public function testSetWebhookSecretKeys()
    {
        $keys = new Keys('', '', ['webhookKey1']);
        $keys->setWebhookSecretKeys([]);
        $this->assertEquals([], $keys->getWebhookSecretKeys());
    }

    public function testGetSecretKey()
    {
        $keys = new Keys('', 'secretKey');
        $this->assertEquals('secretKey', $keys->getSecretKey());
    }

    public function testGetPublishableKey()
    {
        $keys = new Keys('publishableKey', '');
        $this->assertEquals('publishableKey', $keys->getPublishableKey());
    }

    public function testAddWebhookSecretKey()
    {
        $keys = new Keys('', '', []);
        $keys->addWebhookSecretKey('webhookKeyAdded');
        $this->assertEquals(['webhookKeyAdded'], $keys->getWebhookSecretKeys());
    }

    public function testHasPaymentMethodType()
    {
        $keys = new Keys('', '', [], ['card']);

        $this->assertTrue($keys->hasPaymentMethodType('card'));
        $this->assertFalse($keys->hasPaymentMethodType('ideal'));
    }

    public function testGetPaymentMethodTypes()
    {
        $keys = new Keys('', '', [], ['card']);

        $this->assertEquals(['card'], $keys->getPaymentMethodTypes());
    }

    public function testSetPaymentMethodTypes()
    {
        $keys = new Keys('', '', [], ['card']);
        $keys->setPaymentMethodTypes([]);
        $this->assertEquals([], $keys->getPaymentMethodTypes());
    }

    public function testAddPaymentMethodType()
    {
        $keys = new Keys('', '', [], []);
        $keys->addPaymentMethodType('card');
        $this->assertEquals(['card'], $keys->getPaymentMethodTypes());
    }
}
