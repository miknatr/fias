<?php

namespace Fias\ApiAction;

class HttpException extends \Exception
{
    public function __construct($httpCode)
    {
        parent::__construct('', $httpCode);
    }
}
