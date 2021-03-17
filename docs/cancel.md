# Cancel a PaymentIntent

Add those line to the payum builder in `config.php` :

```php
    ->setGenericTokenFactoryPaths([
        'cancel' => 'cancel.php',
    ])
```

Complete the content of [`done.php`](payment.md#donephp) with those lines at the end of the file :

```php
if (in_array($status->getValue(), [
    $status::STATUS_AUTHORIZED,
    $status::STATUS_PENDING,
    $status::STATUS_REFUNDED
])) {
    $tokenFactory = $payum->getTokenFactory();
    $cancelToken = $tokenFactory->createCancelToken($gatewayName, $payment, 'done.php');
    echo '<p><a href="'.$cancelToken->getTargetUrl().'">Cancel</a></p>';
}
```

Finally, create the `cancel.php` file :

> `Cancel` is like the `Refund` action, you can see the base file I used here to make it :
> https://github.com/Payum/Payum/blob/master/docs/examples/refund-script.md

```php
<?php

declare(strict_types=1);

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Cancel;

include __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Cancel($token));

    if (false == isset($_REQUEST['noinvalidate'])) {
        $payum->getHttpRequestVerifier()->invalidate($token);
    }

    if ($token->getAfterUrl()) {
        header("Location: ".$token->getAfterUrl());
    } else {
        http_response_code(204);
        echo 'OK';
    }
} catch (HttpResponse $reply) {
    foreach ($reply->getHeaders() as $name => $value) {
        header("$name: $value");
    }

    http_response_code($reply->getStatusCode());
    echo ($reply->getContent());

    exit;
} catch (ReplyInterface $reply) {
    throw new \LogicException('Unsupported reply', null, $reply);
}
```