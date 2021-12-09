<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\StripeCheckoutSession\Api\StripeCheckoutSessionApiAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Stripe\Checkout\Session;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use StripeCheckoutSessionApiAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false === $details->offsetExists('customer_email')) {
            $details->offsetSet('customer_email', $payment->getClientEmail());
        }

        if (
            Session::MODE_SETUP !== $details->offsetGet('mode')
            && false === $details->offsetExists('line_items')
        ) {
            $details->offsetSet('line_items', [
                [
                    'name' => $payment->getDescription(),
                    'amount' => $payment->getTotalAmount(),
                    'currency' => $payment->getCurrencyCode(),
                    'quantity' => 1,
                ],
            ]);
        }

        $paymentMethodTypes = $this->api->getPaymentMethodTypes();
        if (
            [] !== $paymentMethodTypes
            && false === $details->offsetExists('payment_method_types')
        ) {
            $details->offsetSet('payment_method_types', $paymentMethodTypes);
        }

        $request->setResult($details);
    }

    public function supports($request): bool
    {
        if (false === $request instanceof Convert) {
            return false;
        }

        if ('array' !== $request->getTo()) {
            return false;
        }

        $payment = $request->getSource();
        if (false === $payment instanceof PaymentInterface) {
            return false;
        }

        return true;
    }
}
