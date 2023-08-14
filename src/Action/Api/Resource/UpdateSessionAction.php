<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSession;
use Stripe\Checkout\Session;

final class UpdateSessionAction extends AbstractUpdateAction
{
    protected $apiResourceClass = Session::class;

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateSession;
    }
}
