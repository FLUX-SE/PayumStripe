<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe;

use FluxSE\PayumStripe\AbstractStripeGatewayFactory;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Action\StripeJs\Api\RenderStripeJsAction;
use FluxSE\PayumStripe\Api\KeysInterface;
use FluxSE\PayumStripe\StripeCheckoutSessionGatewayFactory;
use FluxSE\PayumStripe\StripeJsGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;

final class StripeGatewayFactoryTest extends TestCase
{
    /**
     * @dataProvider gatewayList
     */
    public function testShouldBeInstanceOf(string $gatewayClass)
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $this->assertInstanceOf(GatewayFactoryInterface::class, $factory);
        $this->assertInstanceOf(AbstractStripeGatewayFactory::class, $factory);
        $this->assertInstanceOf($gatewayClass, $factory);
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldAllowCreateGatewayConfig(string $gatewayClass)
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(string $gatewayClass)
    {
        $defaults = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass($defaults);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldConfigContainDefaultOptions(string $gatewayClass)
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals([
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret_keys' => [],
            'payment_method_types' => ['card'],
        ], $config['payum.default_options']);
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldThrowIfRequiredOptionsNotPassed(string $gatewayClass)
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The publishable_key, secret_key, webhook_secret_keys fields are required.');

        $factory->create();
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldConfigurePaths(string $gatewayClass)
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));

        $this->assertArrayHasKey('FluxSEPayumStripe', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['FluxSEPayumStripe']);
        $this->assertTrue(file_exists($config['payum.paths']['FluxSEPayumStripe']));
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldAcceptDefaultOptions(string $gatewayClass)
    {
        $defaults = [
            'publishable_key' => '123456',
            'secret_key' => '123456',
            'webhook_secret_keys' => [
                '123456',
            ],
            'payment_method_types' => ['card'],
        ];

        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass($defaults);

        $config = $factory->createConfig();

        $this->assertEquals($defaults['publishable_key'], $config['publishable_key']);
        $this->assertEquals($defaults['secret_key'], $config['secret_key']);
        $this->assertEquals($defaults['webhook_secret_keys'], $config['webhook_secret_keys']);
        $this->assertEquals($defaults['payment_method_types'], $config['payment_method_types']);

        // Allow to update the credentials
        $newCredentials = new ArrayObject([
            'payum.required_options' => $config['payum.required_options'],
            'publishable_key' => '654321',
            'secret_key' => '654321',
            'webhook_secret_keys' => [
                '654321',
            ],
            'payment_method_types' => ['card'],
        ]);
        $api = $config['payum.api']($newCredentials);
        $this->assertInstanceOf(KeysInterface::class, $api);
    }

    public function gatewayList(): array
    {
        return [
            [StripeCheckoutSessionGatewayFactory::class],
            [StripeJsGatewayFactory::class],
        ];
    }

    public function testShouldConfigContainFactoryNameAndTitleForCheckoutSession()
    {
        $factory = new StripeCheckoutSessionGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_checkout_session', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe Checkout Session', $config['payum.factory_title']);

        $redirectToCheckoutAction = $config['payum.action.redirect_to_checkout'](
            ArrayObject::ensureArrayObject($config)
        );
        $this->assertInstanceOf(RedirectToCheckoutAction::class, $redirectToCheckoutAction);
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new StripeJsGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_js', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe JS', $config['payum.factory_title']);

        /** @var RenderStripeJsAction $payAction */
        $payAction = $config['payum.action.render_stripe_js.payment_intent'](ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(RenderStripeJsAction::class, $payAction);
        $this->assertEquals(PaymentIntent::class, $payAction->getApiResourceClass());
        $this->assertEquals($config['payum.template.render_stripe_js.payment_intent'], $payAction->getTemplateName());
    }
}
