<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\WebhookEvent\CheckoutSessionAsyncPaymentFailedAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\WebhookEvent\CheckoutSessionAsyncPaymentSucceededAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\WebhookEvent\CheckoutSessionCompletedAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\AuthorizeAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\CancelAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\CaptureAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\ConvertPaymentAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApi;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;
use Payum\Core\Bridge\Spl\ArrayObject;

final class StripeCheckoutSessionGatewayFactory extends AbstractStripeGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            // Factories
            'payum.factory_name' => 'stripe_checkout_session',
            'payum.factory_title' => 'Stripe Checkout Session',

            // Webhook event resolver
            'payum.action.checkout_session_completed' => new CheckoutSessionCompletedAction(),
            'payum.action.checkout_session_async_payment_failed' => new CheckoutSessionAsyncPaymentFailedAction(),
            'payum.action.checkout_session_async_payment_succeeded' => new CheckoutSessionAsyncPaymentSucceededAction(),

            // Actions
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.redirect_to_checkout' => new RedirectToCheckoutAction(),
            'payum.action.cancel.payment_intent.automatic' => new CancelAction(),

            // Extensions
        ]);

        parent::populateConfig($config);
    }

    protected function getStripeDefaultOptions(): array
    {
        $defaultOptions = parent::getStripeDefaultOptions();
        $defaultOptions['payment_method_types'] = StripeCheckoutSessionApiInterface::DEFAULT_PAYMENT_METHOD_TYPES;

        return $defaultOptions;
    }

    protected function initApi(ArrayObject $config): KeysAwareInterface
    {
        return new StripeCheckoutSessionApi(
            $config['publishable_key'],
            $config['secret_key'],
            $config['webhook_secret_keys'],
            $config['payment_method_types']
        );
    }
}
