# Stripe JS : `PaymentIntent` with `capture_method = manual`

Authorize flow is following this Stripe doc page :
https://stripe.com/docs/payments/capture-later

## Get it started

First get [your credentials](../stripe-credentials.md) from Stripe dashboard.

> The following example is the basic Payum implementation
> (see [documentation of Payum](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md) for more information)

Starting with the configuration of a normal payment, we will have to make some modification on the
`prepare.php` file to generate an `AuthorizeToken`. Then we will trigger the `Authorize` request in an `authorize.php` file.

### prepare.php

```php
<?php

declare(strict_types=1);

include __DIR__.'/config.php';

use Payum\Core\Model\Payment;

$gatewayName = 'stripe_js';

$storage = $payum->getStorage(Payment::class);

/** @var Payment $payment */
$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');
$payment->setDetails([]);

$storage->update($payment);

$tokenFactory = $payum->getTokenFactory();
$captureToken = $tokenFactory->createAuthorizeToken($gatewayName, $payment, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

### authorize.php

```php
<?php

declare(strict_types=1);

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Reply\HttpRedirect;

include __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

if ($reply = $gateway->execute(new Authorize($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    }
    if ($reply instanceof HttpResponse) {
        echo $reply->getContent();
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

$payum->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

## Finally, capture the authorized payment

Complete the content of [`done.php`](payment.md#donephp) with those lines at the end of the file :

```php
if ($status->getValue() === $status::STATUS_AUTHORIZED) {
    $tokenFactory = $payum->getTokenFactory();
    $token = $tokenFactory->createCaptureToken($gatewayName, $payment, 'done.php');
    echo '<a href="'.$token->getTargetUrl().'">Capture</a>';
}
```



