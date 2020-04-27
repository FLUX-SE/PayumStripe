<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\RetrieveSession;
use Stripe\Checkout\Session;

final class RetrieveSessionAction extends AbstractRetrieveAction
{
    /** @var string|Session */
    protected $apiResourceClass = Session::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof RetrieveSession;
    }
}
