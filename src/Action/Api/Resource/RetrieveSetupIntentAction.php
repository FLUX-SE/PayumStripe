<?php

declare(strict_types=1);

namespace Prometee\PayumStripe\Action\Api\Resource;

use Prometee\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use Stripe\SetupIntent;

final class RetrieveSetupIntentAction extends AbstractRetrieveAction
{
    /** @var string|SetupIntent */
    protected $apiResourceClass = SetupIntent::class;

    /**
     * {@inheritDoc}
     */
    public function supportAlso($request): bool
    {
        return $request instanceof RetrieveSetupIntent;
    }
}
