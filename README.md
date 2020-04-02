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

declare(strict_types=1);

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

## Usage quick Example

More effort is needed to get a full working Payum solution, but here is a generic example.

```php
<?php

declare(strict_types=1);

use Payum\Core\Model\Payment;

$gateway = $payum->getGateway('stripe_checkout_session');

$payment = new Payment();
$payment->setNumber('00001');
$payment->setTotalAmount(123);
$payment->setCurrencyCode('USD');
$payment->setClientEmail('test@domain.tld');
$payment->setDescription('My test order');
$payment->setDetails([]);

// Payum create two tokens : an afterToken and a normal one referencing the first one
// So we have one token getting the user on his way to pay and another for webhooks.
$token = $payum->getTokenFactory()
    ->createCaptureToken('stripe_checkout_session', $payment, '/after-pay.php');

$gateway->execute(new Capture($token));
```

## Webhooks

For generic info on Stripe webhooks read this :
https://stripe.com/docs/webhooks

### How the webhooks are handle into this gateway ?

The starting point is always an `Action` with Payum and generically you have to use `Notify*Action` to handle webhooks.

Because we have to set a static url without any token variable params on Stripe backend,
we can't use `NotifyAction` but we can use [`NotifyUnsafeAction`](src/Action/NotifyAction.php) instead.

This `Action` will ask for 2 other actions to :

1. Resolve the webhook event, meaning :
    - retrieve the Stripe signature in the request headers.
    - try to construct the webhook `Event` object checking it with the webhook secret key.
2. Give this resolved `Event` to an `Action` able to consume this `Event`.

So if you want to consume another webhook event type, you just need to create an `Action` extending `Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent\AbstractWebhookEventAction`.
Examples available into the `src/Action/Api/WebhookEvent` folder.

## TODO

 - Add a `RefundAction` if it's possible just with Payum interface.
 - Add Subscription basic webhooks.
 - Add `Extension` or `ConvertPayment` to handle subscription and card storing.

## More

Check the corresponding bundle :

https://github.com/Prometee/PayumStripeCheckoutSessionBundle

And the Sylius plugin :

https://github.com/Prometee/SyliusPayumStripeCheckoutSessionPlugin


[ico-version]: https://img.shields.io/packagist/v/Prometee/payum-stripe-checkout-session.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Prometee/PayumStripeCheckoutSession/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Prometee/PayumStripeCheckoutSession.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/prometee/payum-stripe-checkout-session
[link-travis]: https://travis-ci.org/Prometee/PayumStripeCheckoutSession
[link-scrutinizer]: https://scrutinizer-ci.com/g/Prometee/PayumStripeCheckoutSession/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/Prometee/PayumStripeCheckoutSession
