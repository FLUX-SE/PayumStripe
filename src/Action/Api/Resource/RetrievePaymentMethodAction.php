<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Prometee\PayumStripe\Request\Api\Resource\RetrievePaymentMethod;
use Stripe\PaymentMethod;

final class RetrievePaymentMethodAction extends AbstractRetrieveAction
{
    /** @var string|PaymentMethod */
    protected $apiResourceClass = PaymentMethod::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrievePaymentMethod;
    }
}
