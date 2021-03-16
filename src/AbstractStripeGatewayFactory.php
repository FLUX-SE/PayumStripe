<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\Api\ConstructEventAction;
use FluxSE\PayumStripe\Action\Api\ResolveWebhookEventAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\AllTaxRateAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelPaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CancelSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CapturePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePaymentMethodAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreatePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\CreateTaxRateAction;
use FluxSE\PayumStripe\Action\Api\Resource\DeletePlanAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveChargeAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveCustomerAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveInvoiceAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrievePaymentMethodAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveProductAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSessionAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSetupIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\RetrieveSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdatePaymentIntentAction;
use FluxSE\PayumStripe\Action\Api\Resource\UpdateSubscriptionAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\PaymentIntentCanceledAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\SetupIntentCanceledAction;
use FluxSE\PayumStripe\Action\Api\WebhookEvent\StripeWebhookTestAction;
use FluxSE\PayumStripe\Action\CaptureAuthorizedAction;
use FluxSE\PayumStripe\Action\NotifyAction;
use FluxSE\PayumStripe\Action\StatusAction;
use FluxSE\PayumStripe\Action\StatusPaymentIntentAction;
use FluxSE\PayumStripe\Action\StatusRefundAction;
use FluxSE\PayumStripe\Action\StatusSetupIntentAction;
use FluxSE\PayumStripe\Action\StatusSubscriptionAction;
use FluxSE\PayumStripe\Action\SyncAction;
use FluxSE\PayumStripe\Api\Keys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

abstract class AbstractStripeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            // Actions
            'payum.action.capture_authorized' => new CaptureAuthorizedAction(),
            'payum.action.notify_unsafe' => new NotifyAction(),
            'payum.action.payment_intent_status' => new StatusPaymentIntentAction(),
            'payum.action.setup_intent_status' => new StatusSetupIntentAction(),
            'payum.action.subscription_status' => new StatusSubscriptionAction(),
            'payum.action.refund_status' => new StatusRefundAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),

            // API Resources
            'payum.action.all_customer' => new AllCustomerAction(),
            'payum.action.all_tax_rate' => new AllTaxRateAction(),
            'payum.action.cancel_payment_intent' => new CancelPaymentIntentAction(),
            'payum.action.cancel_subscription' => new CancelSubscriptionAction(),
            'payum.action.capture_payment_intent' => new CapturePaymentIntentAction(),
            'payum.action.create_customer' => new CreateCustomerAction(),
            'payum.action.create_payment_intent' => new CreatePaymentIntentAction(),
            'payum.action.create_payment_method' => new CreatePaymentMethodAction(),
            'payum.action.create_plan' => new CreatePlanAction(),
            'payum.action.create_setup_intent' => new CreateSetupIntentAction(),
            'payum.action.create_session' => new CreateSessionAction(),
            'payum.action.create_subscription' => new CreateSubscriptionAction(),
            'payum.action.create_tax_rate' => new CreateTaxRateAction(),
            'payum.action.delete_plan' => new DeletePlanAction(),
            'payum.action.retrieve_charge' => new RetrieveChargeAction(),
            'payum.action.retrieve_customer' => new RetrieveCustomerAction(),
            'payum.action.retrieve_invoice' => new RetrieveInvoiceAction(),
            'payum.action.retrieve_payment_intent' => new RetrievePaymentIntentAction(),
            'payum.action.retrieve_payment_method' => new RetrievePaymentMethodAction(),
            'payum.action.retrieve_product' => new RetrieveProductAction(),
            'payum.action.retrieve_session' => new RetrieveSessionAction(),
            'payum.action.retrieve_setup_intent' => new RetrieveSetupIntentAction(),
            'payum.action.retrieve_subscription' => new RetrieveSubscriptionAction(),
            'payum.action.update_payment_intent' => new UpdatePaymentIntentAction(),
            'payum.action.update_subscription' => new UpdateSubscriptionAction(),

            // Webhooks
            'payum.action.resolve_webhook_event' => new ResolveWebhookEventAction(),
            'payum.action.construct_event' => new ConstructEventAction(),

            // Webhook event resolver
            'payum.action.stripe_webhook_test' => new StripeWebhookTestAction(),
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

            $config->offsetSet('payum.api', function(ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Keys(
                    $config['publishable_key'],
                    $config['secret_key'],
                    $config['webhook_secret_keys']
                );
            });
        }

        $config->offsetSet('payum.paths', array_replace([
            'FluxSEPayumStripe' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []));
    }
}
