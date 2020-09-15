<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\CreateSession;
use Stripe\Checkout\Session;

final class CreateSessionAction extends AbstractCreateAction
{
    /** @var string|Session */
    protected $apiResourceClass = Session::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso(CreateInterface $request): bool
    {
        return $request instanceof CreateSession;
    }
}
