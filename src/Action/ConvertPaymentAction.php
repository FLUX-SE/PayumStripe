<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = [
            'customer_email' => $payment->getClientEmail(),
            'line_items' => [
                [
                    'name' => $payment->getDescription(),
                    'amount' => $payment->getTotalAmount(),
                    'currency' => $payment->getCurrencyCode(),
                    'quantity' => 1,
                ],
            ],
            'payment_method_types' => [
                'card',
            ],
        ];

        $request->setResult($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}
