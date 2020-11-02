<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use FluxSE\PayumStripe\Request\Api\Resource\RetrieveSetupIntent;
use Stripe\SetupIntent;

final class RetrieveSetupIntentAction extends AbstractRetrieveAction
{
    protected $apiResourceClass = SetupIntent::class;

    public function supportAlso(RetrieveInterface $request): bool
    {
        return $request instanceof RetrieveSetupIntent;
    }
}
