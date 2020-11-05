<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Request\Api\RedirectToCheckout;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

final class RedirectToCheckoutAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use StripeApiAwareTrait {
        StripeApiAwareTrait::__construct as private __stripeApiAwareTraitConstruct;
    }

    /** @var string */
    protected $templateName;

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
        $this->__stripeApiAwareTraitConstruct();
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var RedirectToCheckout $request */
        $renderTemplate = new RenderTemplate($this->templateName, [
            'model' => $request->getModel(),
            'publishable_key' => $this->api->getPublishableKey(),
        ]);

        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return $request instanceof RedirectToCheckout;
    }
}
