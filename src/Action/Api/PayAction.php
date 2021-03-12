<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\Api;

use FluxSE\PayumStripe\Request\Api\Pay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

final class PayAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
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

        /* @var $request Pay */
        $renderTemplate = new RenderTemplate($this->templateName, [
            'model' => $request->getPaymentIntent(),
            'publishable_key' => $this->api->getPublishableKey(),
            'action_url' => $request->getActionUrl(),
        ]);

        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    public function supports($request): bool
    {
        return $request instanceof Pay;
    }
}
