<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeCheckoutSession;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{

    use GatewayAwareTrait;
    use StripeApiAwareTrait {
        StripeApiAwareTrait::__construct as private __stripeApiAwareTraitConstruct;
    }

    public function __construct()
    {
        $this->__stripeApiAwareTraitConstruct();
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false === $details->offsetExists('customer_email')) {
            $details->offsetSet('customer_email', $payment->getClientEmail());
        }

        if (false === $details->offsetExists('line_items')) {
            $details->offsetSet('line_items', [
                [
                    'name' => $payment->getDescription(),
                    'amount' => $payment->getTotalAmount(),
                    'currency' => $payment->getCurrencyCode(),
                    'quantity' => 1,
                ],
            ]);
        }

        if (false === $details->offsetExists('payment_method_types')) {
            $details->offsetSet('payment_method_types', $this->api->getPaymentMethodTypes());
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
