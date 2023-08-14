<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use FluxSE\PayumStripe\Request\Api\Resource\UpdateSetupIntent;
use Stripe\SetupIntent;

final class UpdateSetupIntentAction extends AbstractUpdateAction
{
    protected $apiResourceClass = SetupIntent::class;

    public function supportAlso(UpdateInterface $request): bool
    {
        return $request instanceof UpdateSetupIntent;
    }
}
