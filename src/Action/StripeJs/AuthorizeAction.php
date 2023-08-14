<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs;

use ArrayAccess;
use ArrayObject;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;

/**
 * For more information about Stripe Authorize payments:
 *
 * @see https://stripe.com/docs/payments/capture-later
 */
final class AuthorizeAction extends CaptureAction
{
    public function embedNotifyTokenHash(ArrayObject $model, Generic $request): TokenInterface
    {
        $model->offsetSet('capture_method', 'manual');

        return parent::embedNotifyTokenHash($model, $request);
    }

    /**
     * We are not willing to capture the PaymentIntent here
     */
    protected function capturesIfPaymentIntentStatusCapture(ArrayObject $model, Generic $request): void
    {
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Authorize) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
