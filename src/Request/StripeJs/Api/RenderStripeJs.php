<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\StripeJs\Api;

use Payum\Core\Request\Generic;
use Stripe\ApiResource;

final class RenderStripeJs extends Generic
{
    /** @var string */
    private $actionUrl;

    public function __construct(ApiResource $paymentIntent, string $actionUrl)
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

    public function getApiResource(): ?ApiResource
    {
        $apiResource = $this->getModel();
        if ($apiResource instanceof ApiResource) {
            return $apiResource;
        }

        return null;
    }

    public function setApiResource(ApiResource $apiResource): void
    {
        $this->setModel($apiResource);
    }
}
