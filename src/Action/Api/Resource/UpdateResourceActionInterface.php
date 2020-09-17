<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\UpdateInterface;
use Stripe\ApiResource;

interface UpdateResourceActionInterface extends ResourceActionInterface
{
    public function updateApiResource(UpdateInterface $request): ApiResource;

    public function supportAlso(UpdateInterface $request): bool;
}
