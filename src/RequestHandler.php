<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class RequestHandler
{

    /** @var ConnectionInterface */
    private static $db;
    private static $uri;

    public static function handle($uri, ConnectionInterface $db)
    {
        static::$db  = $db;
        static::$uri = $uri;
    }
}
