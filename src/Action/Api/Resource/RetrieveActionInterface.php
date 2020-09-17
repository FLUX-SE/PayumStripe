<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api\Resource;

use FluxSE\PayumStripe\Request\Api\Resource\RetrieveInterface;
use Stripe\ApiResource;

interface RetrieveActionInterface extends ResourceActionInterface
{
    public function retrieveApiResource(RetrieveInterface $request): ApiResource;

    public function supportAlso(RetrieveInterface $request): bool;
}
