<?php

namespace Tests\FluxSE\PayumStripe\Api;

use FluxSE\PayumStripe\Api\StripeJsApi;
use FluxSE\PayumStripe\Api\StripeJsApiInterface;
use PHPUnit\Framework\TestCase;

final class StripeJsApiTest extends TestCase
{
    use StripeClientAwareApiTrait;

    protected function getApiClass(): string
    {
        return StripeJsApi::class;
    }

    public function test__construct2(): void
    {
        $api = new StripeJsApi('', '');

        $this->assertInstanceOf(StripeJsApiInterface::class, $api);
    }
}
