<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSession;
use Stripe\Checkout\Session;

final class RetrieveSessionAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = Session::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveSession;
    }
}
