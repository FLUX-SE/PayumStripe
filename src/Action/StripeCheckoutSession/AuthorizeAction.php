<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

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
    public function embedOnModeData(ArrayObject $model, TokenInterface $token, string $modeDataKey): void
    {
        parent::embedOnModeData($model, $token, $modeDataKey);

        $embeddedModeData = $model->offsetGet($modeDataKey);
        $embeddedModeData['capture_method'] = 'manual';
        $model->offsetSet($modeDataKey, $embeddedModeData);
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Authorize) {
            return false;
        }

        return $request->getModel() instanceof ArrayAccess;
    }
}
