<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action;

use LogicException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Generic;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;

trait EmbeddableTokenTrait
{
    use GenericTokenFactoryAwareTrait;

    /**
     * Save the token hash for future webhook consuming retrieval.
     *
     *  - A `Session` can be completed.
     *  - or its `PaymentIntent` can be canceled.
     *  - or its `SetupIntent` can be canceled.
     *
     * So the token hash have to be stored both on `Session` metadata and other mode metadata
     */
    public function embedNotifyTokenHash(ArrayObject $model, Generic $request): TokenInterface
    {
        $metadata = $model->offsetGet('metadata');
        if (null === $metadata) {
            $metadata = [];
        }

        $notifyToken = $this->createNotifyToken($request);
        $metadata['token_hash'] = $notifyToken->getHash();
        $model['metadata'] = $metadata;

        return $notifyToken;
    }

    public function createNotifyToken(Generic $request): TokenInterface
    {
        $token = $this->getRequestToken($request);

        return $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );
    }

    public function getRequestToken(Generic $request): TokenInterface
    {
        $token = $request->getToken();

        if (null === $token) {
            throw new LogicException('The request token should not be null !');
        }

        return $token;
    }
}
