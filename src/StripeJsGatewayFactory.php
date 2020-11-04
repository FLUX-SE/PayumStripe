<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe;

use FluxSE\PayumStripe\Action\Api\PayAction;
use FluxSE\PayumStripe\Action\JsCaptureAction;
use Payum\Core\Bridge\Spl\ArrayObject;

final class StripeJsGatewayFactory extends AbstractStripeGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        parent::populateConfig($config);

        $config->defaults([
            // Factory
            'payum.factory_name' => 'stripe_js',
            'payum.factory_title' => 'Stripe JS',

            // Templates
            'payum.template.pay' => '@FluxSEPayumStripe/Action/pay.html.twig',

            // Actions
            'payum.action.capture' => new JsCaptureAction(),
            'payum.action.pay' => function (ArrayObject $config) {
                return new PayAction($config['payum.template.pay']);
            },
        ]);
    }
}
