<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateTaxRate;
use Stripe\TaxRate;

final class CreateTaxRateAction extends AbstractCreateAction
{
    protected $apiResourceClass = TaxRate::class;

    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateTaxRate;
    }
}
