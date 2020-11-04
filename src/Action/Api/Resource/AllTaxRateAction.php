<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllTaxRate;
use Stripe\TaxRate;

final class AllTaxRateAction extends AbstractAllAction
{
    protected $apiResourceClass = TaxRate::class;

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllTaxRate;
    }
}
