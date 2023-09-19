<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe;

use FluxSE\PayumStripe\AbstractStripeGatewayFactory;
use FluxSE\PayumStripe\Action as GlobalAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession;
use FluxSE\PayumStripe\Action\StripeJs;
use FluxSE\PayumStripe\Action\StripeJs\Api\RenderStripeJsAction;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApi;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;
use FluxSE\PayumStripe\Api\StripeClientAwareInterface;
use FluxSE\PayumStripe\Api\StripeJsApi;
use FluxSE\PayumStripe\StripeCheckoutSessionGatewayFactory;
use FluxSE\PayumStripe\StripeJsGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactoryInterface;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use function strpos;

final class StripeGatewayFactoryTest extends TestCase
{
    /**
     * @dataProvider gatewayList
     */
    public function testShouldBeInstanceOf(string $gatewayClass): void
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
    public function testShouldAllowCreateGatewayConfig(string $gatewayClass): void
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
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(string $gatewayClass): void
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
    public function testShouldConfigContainDefaultOptions(string $gatewayClass): void
    {
        /** @var AbstractStripeGatewayFactory $factory */
        $factory = new $gatewayClass();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertArrayHasKey('publishable_key', $config['payum.default_options']);
        $this->assertEquals('', $config['payum.default_options']['publishable_key']);
        $this->assertArrayHasKey('secret_key', $config['payum.default_options']);
        $this->assertEquals('', $config['payum.default_options']['secret_key']);
        $this->assertArrayHasKey('webhook_secret_keys', $config['payum.default_options']);
        $this->assertEquals([], $config['payum.default_options']['webhook_secret_keys']);
        if (StripeCheckoutSessionGatewayFactory::class === $gatewayClass) {
            $this->assertArrayHasKey('payment_method_types', $config['payum.default_options']);
            $this->assertEquals(
                StripeCheckoutSessionApiInterface::DEFAULT_PAYMENT_METHOD_TYPES,
                $config['payum.default_options']['payment_method_types']
            );
        }
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldThrowIfRequiredOptionsNotPassed(string $gatewayClass): void
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
    public function testShouldConfigurePaths(string $gatewayClass): void
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
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('FluxSEPayumStripe', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['FluxSEPayumStripe']);
        $this->assertFileExists($config['payum.paths']['FluxSEPayumStripe']);
    }

    /**
     * @dataProvider gatewayList
     */
    public function testShouldAcceptDefaultOptions(string $gatewayClass): void
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
        $newCredentials->defaults($config);
        $api = $config['payum.api']($newCredentials);
        $this->assertInstanceOf(StripeClientAwareInterface::class, $api);
    }

    public function gatewayList(): array
    {
        return [
            [StripeCheckoutSessionGatewayFactory::class],
            [StripeJsGatewayFactory::class],
        ];
    }

    public function testConfigurationForCheckoutSession(): void
    {
        $defaults = [
            'publishable_key' => '12345',
            'secret_key' => '12345',
            'webhook_secret_keys' => [
                '12345'
            ],
        ];
        $factory = new StripeCheckoutSessionGatewayFactory($defaults);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_checkout_session', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe Checkout Session', $config['payum.factory_title']);

        $actualActions = $this->configuredPayumActions($config);

        $this->assertArrayHasKey('payum.action.capture', $actualActions);
        $this->assertEquals(new StripeCheckoutSession\CaptureAction(), $actualActions['payum.action.capture']);
        $this->assertArrayHasKey('payum.action.authorize', $actualActions);
        $this->assertEquals(new StripeCheckoutSession\AuthorizeAction(), $actualActions['payum.action.authorize']);
        $this->assertArrayHasKey('payum.action.convert_payment', $actualActions);
        $this->assertEquals(new StripeCheckoutSession\ConvertPaymentAction(), $actualActions['payum.action.convert_payment']);
        $this->assertArrayHasKey('payum.action.redirect_to_checkout', $actualActions);
        $this->assertEquals(new StripeCheckoutSession\Api\RedirectToCheckoutAction(), $actualActions['payum.action.redirect_to_checkout']);
        $this->assertArrayHasKey('payum.action.cancel.payment_intent.automatic', $actualActions);
        $this->assertEquals(new StripeCheckoutSession\CancelAction(), $actualActions['payum.action.cancel.payment_intent.automatic']);

        $api = $config['payum.api'](ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(StripeCheckoutSessionApi::class, $api);
    }

    public function testConfigurationForStripeJs(): void
    {
        $defaults = [
            'publishable_key' => '12345',
            'secret_key' => '12345',
            'webhook_secret_keys' => [
                '12345'
            ],
        ];
        $factory = new StripeJsGatewayFactory($defaults);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('stripe_js', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Stripe JS', $config['payum.factory_title']);

        $actualActions = $this->configuredPayumActions($config);

        $this->assertArrayHasKey('payum.action.cancel.payment_intent.manual', $actualActions);
        $this->assertEquals(new GlobalAction\CancelAction(), $actualActions['payum.action.cancel.payment_intent.manual']);
        $this->assertArrayHasKey('payum.action.capture', $actualActions);
        $this->assertEquals(new StripeJs\CaptureAction(), $actualActions['payum.action.capture']);
        $this->assertArrayHasKey('payum.action.authorize', $actualActions);
        $this->assertEquals(new StripeJs\AuthorizeAction(), $actualActions['payum.action.authorize']);
        $this->assertArrayHasKey('payum.action.convert_payment', $actualActions);
        $this->assertEquals(new StripeJs\ConvertPaymentAction(), $actualActions['payum.action.convert_payment']);
        $this->assertArrayHasKey('payum.action.render_stripe_js.payment_intent', $actualActions);
        $this->assertEquals($config['payum.template.render_stripe_js.payment_intent'], $config['payum.template.render_stripe_js.payment_intent']);
        /** @var RenderStripeJsAction $payAction */
        $payAction = $config['payum.action.render_stripe_js.payment_intent'](ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(RenderStripeJsAction::class, $payAction);
        $this->assertEquals($config['payum.template.render_stripe_js.payment_intent'], $payAction->getTemplateName());

        $api = $config['payum.api'](ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(StripeJsApi::class, $api);
    }

    private function configuredPayumActions(array $config): array
    {
        $actions = [];
        foreach ($config as $key => $value) {
            if (strpos($key, 'payum.action.') === 0) {
                $actions[$key] = $value;
            }
        }

        return $actions;
    }
}
