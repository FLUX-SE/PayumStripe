<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveCharge;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\Charge;

final class RetrieveChargeAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Charge::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveCharge;
    }
}

