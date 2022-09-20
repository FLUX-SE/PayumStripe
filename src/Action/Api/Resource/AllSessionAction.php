<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\AllInterface;
use FluxSE\PayumStripe\Request\Api\Resource\AllSession;
use Stripe\Checkout\Session;

final class AllSessionAction extends AbstractAllAction
{
    protected $apiResourceClass = Session::class;

    public function supportAlso(AllInterface $request): bool
    {
        return $request instanceof AllSession;
    }
}
