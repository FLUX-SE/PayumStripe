<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Resources\Views\Action;

use FluxSE\PayumStripe\StripeJsGatewayFactory;
use Payum\Core\Gateway;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

final class StripeJsPaymentIntentTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    protected function guessViewsPath(string $class): string
    {
        $rc = new ReflectionClass($class);

        return dirname($rc->getFileName()).'/Resources/views';
    }

    /**
     * @throws ReflectionException
     * @throws LoaderError
     */
    protected function buildTwigEnvironment(): Environment
    {
        $twigLoader = new FilesystemLoader();
        $twigLoader->addPath(
            $this->guessViewsPath(Gateway::class),
            'PayumCore'
        );
        $twigLoader->addPath(
            $this->guessViewsPath(StripeJsGatewayFactory::class),
            'FluxSEPayumStripe'
        );

        return new Environment($twigLoader);
    }

    /**
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testShouldRenderRedirectToCheckoutTemplate(): void
    {
        $twig = $this->buildTwigEnvironment();

        $result = $twig->render('@FluxSEPayumStripe/Action/stripeJsPaymentIntent.html.twig', [
            'model' => [
                'client_secret' => 'aClientSecret',
            ],
            'publishable_key' => 'theKey',
            'action_url' => 'https://anUrl',
        ]);

        $this->assertStringContainsString('action="https://anUrl"', $result);
        $this->assertStringContainsString('data-secret="aClientSecret"', $result);
        $this->assertStringContainsString('https://js.stripe.com/v3/', $result);
        $this->assertStringContainsString('Stripe(\'theKey\')', $result);
    }
}
