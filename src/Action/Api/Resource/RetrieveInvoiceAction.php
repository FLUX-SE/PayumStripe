<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInvoice;
use Stripe\Invoice;

final class RetrieveInvoiceAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Invoice::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveInvoice;
    }
}

