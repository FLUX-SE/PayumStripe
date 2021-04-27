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

            // Templates
            'payum.template.redirect_to_checkout' => '@FluxSEPayumStripe/Action/redirectToCheckout.html.twig',

            // Webhook event resolver
            'payum.action.checkout_session_completed' => new CheckoutSessionCompletedAction(),

            // Actions
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.redirect_to_checkout' => function (ArrayObject $config) {
                return new RedirectToCheckoutAction($config['payum.template.redirect_to_checkout']);
            },
        ]);
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
