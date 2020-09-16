# Payum Stripe checkout session gateway

See https://stripe.com/docs/payments/checkout for more information.

## Configuration

First get [your credentials](../stripe-credentials.md) from Stripe dashboard.

```php
<?php

declare(strict_types=1);

$loader = require_once( __DIR__.'/vendor/autoload.php');

use Payum\Core\GatewayFactoryInterface;
use FluxSE\PayumStripe\StripeCheckoutSessionGatewayFactory;
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
use Payum\Core\Request\Capture;

$gateway = $payum->getGateway('stripe_checkout_session');

$payment = new Payment();
$payment->setNumber('00001');
$payment->setTotalAmount(123);
$payment->setCurrencyCode('USD');
$payment->setClientEmail('test@domain.tld');
$payment->setDescription('My test order');
$payment->setDetails([]);

$token = $payum->getTokenFactory()
    ->createCaptureToken('stripe_checkout_session', $payment, '/after-pay.php');

$gateway->execute(new Capture($token));
```

## Webhooks

For generic info on Stripe webhooks read this :
https://stripe.com/docs/webhooks

### How the webhooks are handle into this gateway ?

The starting point is always an `Action` with Payum and generically you have to use `Notify*Action` to handle webhooks.

Because we have to set a static url on Stripe backend (eg: without any token variable params),
we have to use what Payum is calling a `NotifyUnsafe`, it's a `Notify` with a `null` `Token`.
You can find this action here : [`NotifyAction.php`](../../src/Action/NotifyAction.php).
If the token is null then we will try to handle a webhook, and if a token is detected
then it's a normal `Notify` so we must handle a `Sync` to refresh a payment details.

#### Resolving a webhook : `NotifyUnsafe`

The [`NotifyAction.php`](../../src/Action/NotifyAction.php) will ask for 2 other actions to :

1. Resolve the webhook event, meaning :
    - retrieve the Stripe signature in the request headers.
    - try to construct the webhook `Event` object, checking it with the webhook secret key.
2. Give this resolved `Event` to an `Action` able to consume this `Event`.

So if you want to consume another webhook event type, you just need to create an `Action`
extending [`FluxSE\PayumStripe\Action\Api\WebhookEvent\AbstractWebhookEventAction`](../../src/Action/Api/WebhookEvent/AbstractWebhookEventAction.php).
Examples available into the [`src/Action/Api/WebhookEvent/`](../../src/Action/Api/WebhookEvent) folder.

## Subscription handling

Payum don't have php `Interfaces` to handle subscription, that's why subscriptions should be
managed by yourself. There is maybe a composer packages which meet your need,
but you will have to build the interface between your subscription `Model` class and `Payum`.

Usually you will have to build a `ConvertPaymentAction` like this one : [ConvertPaymentAction.php](https://github.com/FluxSE/SyliusPayumStripePlugin/blob/master/src/Action/ConvertPaymentAction.php)
customizing the `supports` method to meet your need and finally providing the right `$details` array.

Example : https://stripe.com/docs/payments/checkout/subscriptions/starting#create-checkout-session (`$details` is the array given to create a `Session`)

## Subscription update payment details

Same as the [previous chapter](#subscription-handling)

Example : https://stripe.com/docs/payments/checkout/subscriptions/updating#create-checkout-session (`$details` is the array given to create a `Session`)

## More

 - A Symfony bundle : [flux-se/payum-stripe-checkout-bundle](https://github.com/FluxSE/PayumStripeBundle)
 - A Sylius plugin : [flux-se/sylius-payum-stripe-checkout-session-plugin](https://github.com/FluxSE/SyliusPayumStripePlugin)
  