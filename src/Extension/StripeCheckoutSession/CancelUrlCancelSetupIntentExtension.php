<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Extension\StripeCheckoutSession;

use FluxSE\PayumStripe\Request\Api\Resource\AbstractCustomCall;
use FluxSE\PayumStripe\Request\Api\Resource\CancelSetupIntent;
use Stripe\PaymentIntent;

final class CancelUrlCancelSetupIntentExtension extends AbstractCancelUrlExtension
{
    public function getSupportedObjectName(): string
    {
        return PaymentIntent::OBJECT_NAME;
    }

    public function createNewRequest(string $id): AbstractCustomCall
    {
        return new CancelSetupIntent($id);
    }
}
