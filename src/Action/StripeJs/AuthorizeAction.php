<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\TokenInterface;

/**
 * For more information about Stripe Authorize payments :.
 *
 * @see https://stripe.com/docs/payments/capture-later
 */
final class AuthorizeAction extends CaptureAction
{
    public function embedNotifyTokenHash(ArrayObject $model, TokenInterface $token): void
    {
        parent::embedNotifyTokenHash($model, $token);

        $model->offsetSet('capture_method', 'manual');
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Authorize) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
