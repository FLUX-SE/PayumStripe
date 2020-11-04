<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe;

use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\StripeCheckoutSessionGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;

final class StripeCheckoutSessionGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementCheckoutGatewayFactoryInterface()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $this->assertInstanceOf(GatewayFactoryInterface::class, $factory);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $this->assertInstanceOf(StripeCheckoutSessionGatewayFactory::class, $factory);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new StripeCheckoutSessionGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @test
     */
    public function shouldConfigContainDefaultOptions()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals([
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret_keys' => [],
        ], $config['payum.default_options']);
    }

    /**
     * @test
     */
    public function shouldConfigContainFactoryNameAndTitle()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_checkout_session', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe Checkout Session', $config['payum.factory_title']);
    }

    /**
     * @test
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The publishable_key, secret_key, webhook_secret_keys fields are required.');

        $factory->create();
    }

    /**
     * @test
     */
    public function shouldConfigurePaths()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));

        $this->assertArrayHasKey('FluxSEPayumStripeCheckoutSession', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['FluxSEPayumStripeCheckoutSession']);
        $this->assertTrue(file_exists($config['payum.paths']['FluxSEPayumStripeCheckoutSession']));
    }

    /** @test */
    public function shouldAcceptDefaultOptions()
    {
        $defaults = [
            'publishable_key' => '123456',
            'secret_key' => '123456',
            'webhook_secret_keys' => [
                '123456',
            ],
        ];

        $factory = new StripeCheckoutSessionGatewayFactory($defaults);

        $config = $factory->createConfig();

        $this->assertEquals($defaults['publishable_key'], $config['publishable_key']);
        $this->assertEquals($defaults['secret_key'], $config['secret_key']);
        $this->assertEquals($defaults['webhook_secret_keys'], $config['webhook_secret_keys']);

        // Allow to update the credentials
        $newCredentials = ArrayObject::ensureArrayObject([
            'payum.required_options' => $config['payum.required_options'],
            'publishable_key' => '654321',
            'secret_key' => '654321',
            'webhook_secret_keys' => [
                '654321',
            ],
        ]);
        $api = $config['payum.api']($newCredentials);
        $this->assertInstanceOf(KeysInterface::class, $api);
    }
}
