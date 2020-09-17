<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\CreateInterface;
use Stripe\ApiResource;

interface CreateResourceActionInterface extends ResourceActionInterface
{
    public function createApiResource(CreateInterface $request): ApiResource;

    public function supportAlso(CreateInterface $request): bool;
}
