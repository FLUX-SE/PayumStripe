<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\Api\ConstructEventAction;
use FluxSE\PayumStripe\Action\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Action\Api\ResolveWebhookEventAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\CheckoutSessionCompletedAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\PaymentIntentCanceledAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\SetupIntentCanceledAction;
use FluxSE\PayumStripe\Action\CaptureAction;
use FluxSE\PayumStripe\Action\ConvertPaymentAction;
use FluxSE\PayumStripe\Action\NotifyAction;
use FluxSE\PayumStripe\Action\StatusAction;
use FluxSE\PayumStripe\Action\SyncAction;
use FluxSE\PayumStripe\Action\SyncSetupIntentAction;
use FluxSE\PayumStripe\Action\SyncSubscriptionAction;
use FluxSE\PayumStripe\Api\Keys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

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
            'payum.template.redirect_to_checkout' => '@FluxSEPayumStripeCheckoutSession/Action/redirectToCheckout.html.twig',

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
            'payum.action.create_customer' => new CreateCustomerAction(),
            'payum.action.create_plan' => new CreatePlanAction(),
            'payum.action.create_subscription' => new CreateSubscriptionAction(),
            'payum.action.retrieve_session' => new RetrieveSessionAction(),
            'payum.action.retrieve_payment_intent' => new RetrievePaymentIntentAction(),
            'payum.action.retrieve_subscription' => new RetrieveSubscriptionAction(),
            'payum.action.retrieve_setup_intent' => new RetrieveSetupIntentAction(),

            // Webhooks
            'payum.action.resolve_webhook_event' => new ResolveWebhookEventAction(),
            'payum.action.construct_event' => new ConstructEventAction(),

            // Webhook event resolver
            'payum.action.checkout_session_completed' => new CheckoutSessionCompletedAction(),
            'payum.action.payment_intent_canceled' => new PaymentIntentCanceledAction(),
            'payum.action.setup_intent_canceled' => new SetupIntentCanceledAction(),
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
            'FluxSEPayumStripeCheckoutSession' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []));
    }
}
