<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\Api\RedirectToCheckoutAction;
use FluxSE\PayumStripe\Action\CaptureAction;
use Payum\Core\Bridge\Spl\ArrayObject;

final class StripeCheckoutSessionGatewayFactory extends AbstractStripeGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        parent::populateConfig($config);

        $config->defaults([
            // Factory
            'payum.factory_name' => 'stripe_checkout_session',
            'payum.factory_title' => 'Stripe Checkout Session',

            // Templates
            'payum.template.redirect_to_checkout' => '@FluxSEPayumStripe/Action/redirectToCheckout.html.twig',

            // Action
            'payum.action.capture' => new CaptureAction(),
            'payum.action.redirect_to_checkout' => function (ArrayObject $config) {
                return new RedirectToCheckoutAction($config['payum.template.redirect_to_checkout']);
            },
        ]);
    }
}
