<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action\Api\WebhookEvent;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Sync;
use Payum\Core\Security\TokenInterface;
use Prometee\PayumStripeCheckoutSession\Request\Api\WebhookEvent\WebhookEvent;
use Prometee\PayumStripeCheckoutSession\Request\DeleteWebhookToken;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

abstract class AbstractPaymentAction extends AbstractWebhookEventAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param WebhookEvent $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $eventWrapper = $request->getEventWrapper();
        $event = $eventWrapper->getEvent();

        /** @var Session|PaymentIntent $sessionOrPaymentIntent */
        $sessionOrPaymentIntent = $event->data->offsetGet('object');

        /** @var string|null $tokenHash */
        $tokenHash = $sessionOrPaymentIntent->metadata->offsetGet('token_hash');

        if ($tokenHash === null) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        // 1. Try to found the Token
        $token = $this->findTokenByHash($tokenHash);
        // 2. Try to found the Status of this Token
        $status = $this->findStatusByToken($token);

        // 3. Retrieve the PaymentIntent from Session or PaymentIntent
        $details = ArrayObject::ensureArrayObject($sessionOrPaymentIntent->toArray());
        $this->gateway->execute(new Sync($details));

        // 4. Update the payment
        $payment = $status->getFirstModel();
        // We can't rely on interfaces sometime
        // ex: Sylius don't use Payum\Core\Model\PaymentInterface
        //     or any Payum Payment related interfaces
        if (method_exists($payment, 'setDetails')) {
            $payment->setDetails($details->toUnsafeArray());
        }

        // 5. Finally delete the used token
        $this->gateway->execute(new DeleteWebhookToken($token));
    }

    /**
     * @param string $tokenHash
     *
     * @return TokenInterface
     */
    private function findTokenByHash(string $tokenHash): TokenInterface
    {
        $getTokenRequest = new GetToken($tokenHash);

        $this->gateway->execute($getTokenRequest);

        return $getTokenRequest->getToken();
    }

    /**
     * @param TokenInterface $token
     *
     * @return GetBinaryStatus
     */
    private function findStatusByToken(TokenInterface $token): GetBinaryStatus
    {
        $status = new GetBinaryStatus($token);
        $this->gateway->execute($status);

        return $status;
    }
}
