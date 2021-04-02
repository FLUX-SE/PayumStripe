<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Token;

interface TokenHashKeysInterface
{
    public const DEFAULT_TOKEN_HASH_KEY_NAME = 'token_hash';
    public const CAPTURE_AUTHORIZE_TOKEN_HASH_KEY_NAME = 'capture_authorize_token_hash';
    public const CANCEL_TOKEN_HASH_KEY_NAME = 'cancel_authorized_token_hash';
    public const REFUND_TOKEN_HASH_KEY_NAME = 'refund_token_hash';
}
