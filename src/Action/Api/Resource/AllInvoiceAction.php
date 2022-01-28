<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllInvoice;
use Stripe\Invoice;

final class AllInvoiceAction extends AbstractAllAction
{
    protected $apiResourceClass = Invoice::class;

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllInvoice;
    }
}
