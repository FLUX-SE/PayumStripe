<?php

declare(strict_types=1);

namespace Prometee\PayumStripeCheckoutSession\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Storage\StorageInterface;
use Prometee\PayumStripeCheckoutSession\Request\DeleteWebhookToken;

class DeleteWebhookTokenAction implements ActionInterface
{
    /**
     * @var StorageInterface
     */
    private $tokenStorage;

    /**
     * @param StorageInterface $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     *
     * @param DeleteWebhookToken $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->tokenStorage->delete($request->getToken());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof DeleteWebhookToken
        ;
    }
}
