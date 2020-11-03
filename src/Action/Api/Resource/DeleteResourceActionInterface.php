<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\DeleteInterface;
use Stripe\ApiResource;

interface DeleteResourceActionInterface extends ResourceActionInterface
{
    public function deleteApiResource(DeleteInterface $request): ApiResource;

    public function supportAlso(DeleteInterface $request): bool;
}
