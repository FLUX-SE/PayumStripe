<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Action\StripeJs\Api;

use FluxSE\PayumStripe\Action\Api\StripeApiAwareTrait;
use FluxSE\PayumStripe\Request\StripeJs\Api\RenderStripeJs;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

final class RenderStripeJsAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use StripeApiAwareTrait {
        StripeApiAwareTrait::__construct as private __stripeApiAwareTraitConstruct;
    }

    /** @var string */
    private $templateName;

    /** @var string */
    private $apiResourceClass;

    public function __construct(string $templateName, string $apiResourceClass)
    {
        $this->templateName = $templateName;
        $this->apiResourceClass = $apiResourceClass;
        $this->__stripeApiAwareTraitConstruct();
    }

    /**
     * @param RenderStripeJs $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $renderTemplate = new RenderTemplate($this->templateName, [
            'model' => $request->getApiResource(),
            'publishable_key' => $this->api->getPublishableKey(),
            'action_url' => $request->getActionUrl(),
        ]);

        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    public function supports($request): bool
    {
        if (false === $request instanceof RenderStripeJs) {
            return false;
        }

        return $request->getApiResource() instanceof $this->apiResourceClass;
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getApiResourceClass(): string
    {
        return $this->apiResourceClass;
    }
}
