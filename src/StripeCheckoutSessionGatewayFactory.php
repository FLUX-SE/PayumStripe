<?php

declare(strict_types=1);

namespace Prometee\PayumStripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Prometee\PayumStripe\Action\Api\ConstructEventAction;
use Prometee\PayumStripe\Action\Api\RedirectToCheckoutAction;
use Prometee\PayumStripe\Action\Api\ResolveWebhookEventAction;
use Prometee\PayumStripe\Action\Api\Resource\CreateCustomerAction;
use Prometee\PayumStripe\Action\Api\Resource\CreatePlanAction;
use Prometee\PayumStripe\Action\Api\Resource\CreateSessionAction;
use Prometee\PayumStripe\Action\Api\Resource\CreateSubscriptionAction;
use Prometee\PayumStripe\Action\Api\Resource\RetrievePaymentIntentAction;
use Prometee\PayumStripe\Action\Api\WebhookEvent\CheckoutSessionCompletedAction;
use Prometee\PayumStripe\Action\Api\WebhookEvent\PaymentIntentCanceledAction;
use Prometee\PayumStripe\Action\CaptureAction;
use Prometee\PayumStripe\Action\ConvertPaymentAction;
use Prometee\PayumStripe\Action\NotifyAction;
use Prometee\PayumStripe\Action\StatusAction;
use Prometee\PayumStripe\Action\SyncAction;
use Prometee\PayumStripe\Api\Keys;

class StripeCheckoutSessionGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config): void
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
            'payum.action.notify_unsafe' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.redirect_to_checkout' => function (ArrayObject $config) {
                return new RedirectToCheckoutAction($config['payum.template.redirect_to_checkout']);
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

        if (false === $config->offsetExists('payum.api')) {
            $config->offsetSet('payum.default_options', [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret_keys' => [],
            ]);
            $config->defaults($config['payum.default_options']);
            $config->offsetSet('payum.required_options', [
                'publishable_key',
                'secret_key',
                'webhook_secret_keys',
            ]);

            $config->offsetSet('payum.api', function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Keys(
                    $config['publishable_key'],
                    $config['secret_key'],
                    $config['webhook_secret_keys']
                );
            });
        }

        $config->offsetSet('payum.paths', array_replace([
            'PrometeePayumStripeCheckoutSession' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []));
    }
}
