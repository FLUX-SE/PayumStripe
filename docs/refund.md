# Refund a PaymentIntent

Complete the content of [`done.php`](payment.md#donephp) with those lines at the end of the file :

```php
if ($status->getValue() === $status::STATUS_CAPTURED) {
    $tokenFactory = $payum->getTokenFactory();
    $refundToken = $tokenFactory->createCancelToken($gatewayName, $payment, 'done.php');
    echo '<p><a href="'.$refundToken->getTargetUrl().'">Refund</a></p>';
}
```

Finally, create the `refund.php` file :

```php
<?php

declare(strict_types=1);

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Refund;

include __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Refund($token));

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