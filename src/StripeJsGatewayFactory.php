<?php

declare(strict_types=1);

namespace Prometee\PayumStripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Prometee\PayumStripe\Action\Api\PayAction;
use Prometee\PayumStripe\Action\JsCaptureAction;

class StripeJsGatewayFactory extends StripeCheckoutSessionGatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'stripe_js',
            'payum.factory_title' => 'Stripe JS',

            'payum.action.capture' => new JsCaptureAction(),
            'payum.action.pay' => function (ArrayObject $config) {
                return new PayAction($config['payum.template.pay']);
            },
            'payum.template.pay' => '@PrometeePayumStripeCheckoutSession/Action/pay.html.twig',
        ]);

        parent::populateConfig($config);
    }
}
