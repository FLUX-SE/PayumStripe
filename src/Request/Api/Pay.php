<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api;

use LogicException;
use Payum\Core\Request\Generic;
use Stripe\PaymentIntent;

final class Pay extends Generic
{
    /** @var string */
    private $actionUrl;

    public function __construct(PaymentIntent $paymentIntent, string $actionUrl)
    {
        parent::__construct($paymentIntent);
        $this->actionUrl = $actionUrl;
    }

    public function getActionUrl(): string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(string $actionUrl): void
    {
        $this->actionUrl = $actionUrl;
    }

    public function getPaymentIntent(): PaymentIntent
    {
        $paymentIntent = $this->getModel();
        if ($paymentIntent instanceof PaymentIntent) {
            return $paymentIntent;
        }

        throw new LogicException(sprintf('The model is not an instance of "%s" !', PaymentIntent::class));
    }

    public function setPaymentIntent(PaymentIntent $paymentIntent): void
    {
        $this->setModel($paymentIntent);
    }
}
