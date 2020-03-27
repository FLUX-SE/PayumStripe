<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Prometee\PayumStripeCheckoutSession\Action\Api\ConstructEventAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\RedirectToCheckoutAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\ResolveWebhookEventAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreateCustomerAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreatePlanAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreateSessionAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\CreateSubscriptionAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\Resource\RetrievePaymentIntentAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\CheckoutSessionCompletedAction;
use Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\PaymentIntentCanceledAction;
use Prometee\PayumStripeCheckoutSession\Action\CaptureAction;
use Prometee\PayumStripeCheckoutSession\Action\ConvertPaymentAction;
use Prometee\PayumStripeCheckoutSession\Action\DeleteWebhookTokenAction;
use Prometee\PayumStripeCheckoutSession\Action\NotifyUnsafeAction;
use Prometee\PayumStripeCheckoutSession\Action\StatusAction;
use Prometee\PayumStripeCheckoutSession\Action\SyncAction;
use Prometee\PayumStripeCheckoutSession\Api\Keys;

class StripeCheckoutSessionGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            // Factory
            'payum.factory_name' => 'stripe_checkout_session',
            'payum.factory_title' => 'Stripe Checkout Session',

            // Templates
            'payum.template.redirect_to_checkout' => '@PrometeePayumStripeCheckoutSession/Action/redirectToCheckout.html.twig',

            // Actions
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify_unsafe' => new NotifyUnsafeAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.redirect_to_checkout' => function (ArrayObject $config) {
                return new RedirectToCheckoutAction($config['payum.template.redirect_to_checkout']);
            },
            'payum.action.delete_webhook_token' => function (ArrayObject $config) {
                return new DeleteWebhookTokenAction($config['payum.security.token_storage']);
            },

            // API Resources
            'payum.action.create_session' => new CreateSessionAction(),
            'payum.action.retrieve_payment_intent' => new RetrievePaymentIntentAction(),
            'payum.action.create_customer' => new CreateCustomerAction(),
            'payum.action.create_plan' => new CreatePlanAction(),
            'payum.action.create_subscription' => new CreateSubscriptionAction(),

            // Webhooks
            'payum.action.resolve_webhook_event' => new ResolveWebhookEventAction(),
            'payum.action.construct_event' => new ConstructEventAction(),
            // Webhook event resolver
            'payum.action.checkout_session_completed' => new CheckoutSessionCompletedAction(),
            'payum.action.payment_intent_canceled' => new PaymentIntentCanceledAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret_keys' => [],
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'publishable_key',
                'secret_key',
                'webhook_secret_keys',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Keys(
                    $config['publishable_key'],
                    $config['secret_key'],
                    $config['webhook_secret_keys']
                );
            };
        }

        $config['payum.paths'] = array_replace([
            'PrometeePayumStripeCheckoutSession' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
