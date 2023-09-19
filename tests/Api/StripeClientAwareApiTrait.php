<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Api;

use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use ReflectionClass;
use Stripe\StripeClient;

trait StripeClientAwareApiTrait
{
    use KeysAwareApiTrait;
    abstract protected function getApiClass(): string;

    protected function getReflectionApiClass(): ReflectionClass
    {
        return new ReflectionClass($this->getApiClass());
    }

    public function test__construct(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '');

        $this->assertInstanceOf(StripeClientAwareInterface::class, $api);
    }

    public function testGetClientId(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', [], '12345');
        $this->assertEquals('12345', $api->getClientId());
    }

    public function testGetStripeAccount(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', [], null, '12345');
        $this->assertEquals('12345', $api->getStripeAccount());
    }

    public function testGetStripeVersion(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '', [], null, null, '1');
        $this->assertEquals('1', $api->getStripeVersion());
    }

    public function testGetStripeClient(): void
    {
        $api = $this->getReflectionApiClass()->newInstance('', '12345');
        $this->assertInstanceOf(StripeClient::class, $api->getStripeClient());
    }
}
