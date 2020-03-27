<?php

declare(strict_types=1);

namespace Tests\Prometee\PayumStripeCheckoutSession;

use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;
use Prometee\PayumStripeCheckoutSession\StripeCheckoutSessionGatewayFactory;

class StripeCheckoutSessionGatewayFactoryTest extends TestCase
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
        $factory = new StripeCheckoutSessionGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

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

        $this->assertArrayHasKey('PrometeePayumStripeCheckoutSession', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PrometeePayumStripeCheckoutSession']);
        $this->assertTrue(file_exists($config['payum.paths']['PrometeePayumStripeCheckoutSession']));
    }
}
