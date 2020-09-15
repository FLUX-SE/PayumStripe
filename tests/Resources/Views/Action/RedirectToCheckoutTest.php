<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Resources\Views\Action;

use Payum\Core\Gateway;
use PHPUnit\Framework\TestCase;
use FluxSE\PayumStripe\StripeCheckoutSessionGatewayFactory;
use ReflectionClass;
use ReflectionException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

final class RedirectToCheckoutTest extends TestCase
{
    /**
     * @param string $class
     *
     * @return string
     *
     * @throws ReflectionException
     */
    protected function guessViewsPath(string $class): string
    {
        $rc = new ReflectionClass($class);
        return dirname($rc->getFileName()) . '/Resources/views';
    }

    /**
     * @return Environment
     *
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
            $this->guessViewsPath(StripeCheckoutSessionGatewayFactory::class),
            'FluxSEPayumStripeCheckoutSession'
        );
        return new Environment($twigLoader);
    }

    /**
     * @test
     *
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function shouldRenderRedirectToCheckoutTemplate()
    {
        $twig = $this->buildTwigEnvironment();

        $result = $twig->render('@FluxSEPayumStripeCheckoutSession/Action/redirectToCheckout.html.twig', [
            'model' => [
                'id' => 1
            ],
            'publishable_key' => 'theKey',
        ]);

        $this->assertStringContainsString('sessionId: \'1\'', $result);
        $this->assertStringContainsString('(\'theKey\')', $result);
        $this->assertStringContainsString('https://js.stripe.com/v3/', $result);
    }
}
