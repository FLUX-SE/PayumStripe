<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details->offsetSet('customer_email', $payment->getClientEmail());
        $details->offsetSet('line_items', [
            [
                'name' => $payment->getDescription(),
                'amount' => $payment->getTotalAmount(),
                'currency' => $payment->getCurrencyCode(),
                'quantity' => 1,
            ],
        ]);
        $details->offsetSet('payment_method_types', [
            'card',
        ]);

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
