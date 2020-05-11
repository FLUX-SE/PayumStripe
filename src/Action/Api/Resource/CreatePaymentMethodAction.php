<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use App\Entity\Payment\PaymentMethod;
use Prometee\PayumStripe\Request\Api\Resource\CreateInterface;
use Prometee\PayumStripe\Request\Api\Resource\CreatePaymentMethod;

class CreatePaymentMethodAction extends AbstractCreateAction
{
    /** @var string|PaymentMethod */
    protected $apiResourceClass = PaymentMethod::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreatePaymentMethod;
    }
}
