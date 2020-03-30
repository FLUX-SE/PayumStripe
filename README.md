[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

## Payum Stripe checkout session gateway

This library is designed to add a new gateway to Payum to support Stripe checkout session

See https://stripe.com/docs/payments/checkout for more information.

## Installation

Install using Composer :

```bash
composer require prometee/payum-stripe-checkout-session
```

Choose one of [php-http/client-implementation](https://packagist.org/providers/php-http/client-implementation),
the most used is [php-http/guzzle6-adapter](https://packagist.org/packages/php-http/guzzle6-adapter)

```bash
composer require  php-http/guzzle6-adapter
```

## Configuration

### API keys

Get your `publishable_key` and your `secret_key` on your Stripe account :

https://dashboard.stripe.com/test/apikeys

### Webhook key
Then get a `webhook_secret_key` configured with at least two events : 
`payment_intent.canceled` and `checkout.session.completed`

https://dashboard.stripe.com/test/webhooks

```php
<?php

$loader = require_once( __DIR__.'/vendor/autoload.php');

use Payum\Core\GatewayFactoryInterface;
use Prometee\PayumStripeCheckoutSession\StripeCheckoutSessionGatewayFactory;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('stripe_checkout_session', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new StripeCheckoutSessionGatewayFactory($config, $coreGatewayFactory);
    })
    ->addGateway('stripe_checkout_session', [
        'factory' => 'stripe_checkout_session',
        'publishable_key' => 'pk_test_abcdefghijklmnopqrstuvwx',
        'secret_key' => 'sk_test_abcdefghijklmnopqrstuvwx',
        'webhook_secret_keys' => [
            'whsec_abcdefghijklmnopqrstuvwxyz012345'
        ],
    ])
    ->getPayum()
;
```

## Usage

```php
<?php

use ArrayObject;
use Payum\Core\Request\Capture;

$gateway = $payum->getGateway('stripe_checkout_session');

$model = new ArrayObject([
    // ...
]);

$gateway->execute(new Capture($model));
```

[ico-version]: https://img.shields.io/packagist/v/Prometee/payum-stripe-checkout-session.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Prometee/PayumStripeCheckoutSession/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Prometee/PayumStripeCheckoutSession.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/prometee/payum-stripe-checkout-session
[link-travis]: https://travis-ci.org/Prometee/PayumStripeCheckoutSession
[link-scrutinizer]: https://scrutinizer-ci.com/g/Prometee/PayumStripeCheckoutSession/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Prometee/PayumStripeCheckoutSession
