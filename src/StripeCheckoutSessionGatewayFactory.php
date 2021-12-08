<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\WebhookEvent\CheckoutSessionCompletedAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\AuthorizeAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\CaptureAction;
use FluxSE\PayumStripe\Action\StripeCheckoutSession\ConvertPaymentAction;
use FluxSE\PayumStripe\Api\KeysAwareInterface;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApi;
use FluxSE\PayumStripe\Api\StripeCheckoutSessionApiInterface;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlCancelPaymentIntentExtension;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlCancelSetupIntentExtension;
use FluxSE\PayumStripe\Extension\StripeCheckoutSession\CancelUrlExpireSessionExtension;
use Payum\Core\Bridge\Spl\ArrayObject;

final class StripeCheckoutSessionGatewayFactory extends AbstractStripeGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        parent::populateConfig($config);

        $config->defaults([
            // Factories
            'payum.factory_name' => 'stripe_checkout_session',
            'payum.factory_title' => 'Stripe Checkout Session',

            // Webhook event resolver
            'payum.action.checkout_session_completed' => new CheckoutSessionCompletedAction(),

            // Actions
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.redirect_to_checkout' => new RedirectToCheckoutAction(),

            // Extensions
            'payum.extension.after_capture_cancel_payment_intent' => new CancelUrlCancelPaymentIntentExtension(),
            'payum.extension.after_capture_cancel_setup_intent' => new CancelUrlCancelSetupIntentExtension(),
            'payum.extension.after_capture_expire_session' => new CancelUrlExpireSessionExtension(),
        ]);
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
