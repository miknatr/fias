<?php

namespace Fias\ApiAction;

class HttpException extends \Exception
{
    public function __construct($httpStatusCode)
    {
        parent::__construct('', $httpStatusCode);
    }
}
