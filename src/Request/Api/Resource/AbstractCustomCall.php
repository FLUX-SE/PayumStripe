<?php

declare(strict_types=1);

namespace FluxSE\PayumStripe\Request\Api\Resource;

abstract class AbstractCustomCall extends AbstractRetrieve implements CustomCallInterface
{
    use CustomCallTrait;

    public function __construct(
        string $id,
        array $retrieveOptions = [],
        array $customCallParameters = [],
        array $customCallOptions = []
    ) {
        $this->customCallParameters = $customCallParameters;
        $this->customCallOptions = $customCallOptions;
        parent::__construct($id, $retrieveOptions);
    }
}
