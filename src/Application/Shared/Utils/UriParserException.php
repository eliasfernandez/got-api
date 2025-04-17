<?php

namespace App\Application\Shared\Utils;

final class UriParserException extends \RuntimeException
{
    public function __construct($object)
    {
        parent::__construct(sprintf('Not valid Uri. %s given but string expected', gettype($object)));
    }
}
