<?php

declare(strict_types=1);

namespace Tests\FluxSE\PayumStripe\Stripe;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;
use Stripe\Stripe;
use function in_array;
use function json_encode;
use function strtolower;

/**
 * Helper trait for Stripe test cases.
 */
trait StripeApiTestHelper
{
    /** @var string|null original API base URL */
    protected $origApiBase;

    /** @var string|null original API key */
    protected $origApiKey;

    /** @var string|null original client ID */
    protected $origClientId;

    /** @var string|null original API version */
    protected $origApiVersion;

    /** @var string|null original account ID */
    protected $origAccountId;

    /** @var MockObject&ClientInterface HTTP client mocker */
    protected $clientMock;

    /**
     * Returns a mock object for the specified class.
     *
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     */
    abstract protected function createMock(string $originalClassName): MockObject;

    /** @before */
    protected function setUpConfig(): void
    {
        // Save original values so that we can restore them after running tests
        $this->origApiBase = Stripe::$apiBase;
        $this->origApiKey = Stripe::getApiKey();
        $this->origClientId = Stripe::getClientId();
        $this->origApiVersion = Stripe::getApiVersion();
        $this->origAccountId = Stripe::getAccountId();

        // Set up host and credentials for stripe-mock
        Stripe::setApiKey('sk_test_123');
        Stripe::setClientId('ca_123');
        Stripe::setApiVersion(null);
        Stripe::setAccountId(null);

        // Set up the HTTP client mocker
        $this->clientMock = $this->createMock(ClientInterface::class);

        // By default, use the real HTTP client
        ApiRequestor::setHttpClient(CurlClient::instance());
    }

    /** @after */
    protected function tearDownConfig(): void
    {
        // Restore original values
        Stripe::$apiBase = $this->origApiBase;
        Stripe::setEnableTelemetry(false);
        Stripe::setApiKey($this->origApiKey);
        Stripe::setClientId($this->origClientId);
        Stripe::setApiVersion($this->origApiVersion);
        Stripe::setAccountId($this->origAccountId);
    }

    /**
     * Sets up a request expectation with the provided parameters. The request
     * will actually go through and be emitted.
     *
     * @param string        $method  HTTP method (e.g. 'post', 'get', etc.)
     * @param string        $path    relative path (e.g. '/v1/charges')
     * @param array|null    $params  array of parameters. If null, parameters will
     *                               not be checked.
     * @param string[]|null $headers array of headers. Does not need to be
     *                               exhaustive. If null, headers are not checked.
     * @param bool          $hasFile Whether the request parameters contains a file.
     *                               Defaults to false.
     * @param string|null   $base    base URL (e.g. 'https://api.stripe.com')
     */
    protected function expectsRequest(
        string $method,
        string $path,
        ?array $params = null,
        ?array $headers = null,
        bool $hasFile = false,
        ?string $base = null
    ): void {
        $this->prepareRequestMock($method, $path, $params, $headers, $hasFile, $base)
            ->willReturnCallback(
                function ($method, $absUrl, $headers, $params, $hasFile) {
                    $curlClient = CurlClient::instance();
                    ApiRequestor::setHttpClient($curlClient);

                    return $curlClient->request($method, $absUrl, $headers, $params, $hasFile);
                }
            )
        ;
    }

    /**
     * Sets up a request expectation with the provided parameters. The request
     * will not actually be emitted, instead the provided response parameters
     * will be returned.
     *
     * @param string        $method  HTTP method (e.g. 'post', 'get', etc.)
     * @param string        $path    relative path (e.g. '/v1/charges')
     * @param array|null    $params  array of parameters. If null, parameters will
     *                               not be checked.
     * @param string[]|null $headers array of headers. Does not need to be
     *                               exhaustive. If null, headers are not checked.
     * @param bool          $hasFile Whether the request parameters contains a file.
     *                               Defaults to false.
     */
    protected function stubRequest(
        string $method,
        string $path,
        ?array $params = null,
        ?array $headers = null,
        bool $hasFile = false,
        array $response = [],
        int $rcode = 200,
        ?string $base = null
    ): void {
        $this->prepareRequestMock($method, $path, $params, $headers, $hasFile, $base)
            ->willReturn([json_encode($response), $rcode, []])
        ;
    }

    /**
     * Prepares the client mocker for an invocation of the `request` method.
     * This helper method is used by both `expectsRequest` and `stubRequest` to
     * prepare the client mocker to expect an invocation of the `request` method
     * with the provided arguments.
     *
     * @param string        $method  HTTP method (e.g. 'post', 'get', etc.)
     * @param string        $path    relative path (e.g. '/v1/charges')
     * @param array|null    $params  array of parameters. If null, parameters will
     *                               not be checked.
     * @param string[]|null $headers array of headers. Does not need to be
     *                               exhaustive. If null, headers are not checked.
     * @param bool          $hasFile Whether the request parameters contains a file.
     *                               Defaults to false.
     * @param string|null   $base    base URL (e.g. 'https://api.stripe.com')
     */
    private function prepareRequestMock(
        string $method,
        string $path,
        ?array $params = null,
        ?array $headers = null,
        bool $hasFile = false,
        ?string $base = null
    ): InvocationMocker {
        ApiRequestor::setHttpClient($this->clientMock);

        if (null === $base) {
            $base = Stripe::$apiBase;
        }
        $absUrl = $base . $path;

        return $this->clientMock
            ->expects(TestCase::once())
            ->method('request')
            ->with(
                TestCase::identicalTo(strtolower($method)),
                TestCase::identicalTo($absUrl),
                // for headers, we only check that all of the headers provided in $headers are
                // present in the list of headers of the actual request
                null === $headers ? TestCase::anything() : TestCase::callback(function ($array) use ($headers) {
                    foreach ($headers as $header) {
                        if (!in_array($header, $array, true)) {
                            return false;
                        }
                    }

                    return true;
                }),
                null === $params ? TestCase::anything() : TestCase::identicalTo($params),
                TestCase::identicalTo($hasFile)
            )
        ;
    }
}
