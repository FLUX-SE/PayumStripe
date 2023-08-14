<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use ArrayAccess;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // @link https://stripe.com/docs/api/payment_intents/cancel
        // > You cannot cancel the PaymentIntent created by a Checkout Session.
        // > Expire the Checkout Session instead.
        $allSessionRequest = new AllSession(
            [
                'payment_intent' => $model['id'],
            ]
        );
        $this->gateway->execute($allSessionRequest);

        $sessions = $allSessionRequest->getApiResources();
        $session = $sessions->first();
        if (!$session instanceof Session) {
            return;
        }

        $this->gateway->execute(new Cancel($session->toArray()));
    }

    public function supports($request): bool
    {
        if (!$request instanceof Cancel) {
            return false;
        }

        $model = $request->getModel();
        if (!$model instanceof ArrayAccess) {
            return false;
        }

        if (PaymentIntent::OBJECT_NAME !== $model->offsetGet('object')) {
            return false;
        }

        // if capture_method=automatic it means the payment intent was created from a checkout session without authorization
        return $model->offsetExists('capture_method') && $model->offsetGet('capture_method') === 'automatic';
    }
}
