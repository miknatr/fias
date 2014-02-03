<?php

namespace Fias\ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Handler
{
    public static function handleRequest($uri, $params, ConnectionInterface $db)
    {
        $action = rtrim(explode('?', $uri, 2)[0], '/');
        switch ($action) {
            case '/api/complete':
                return (new Completion($db, $params))->run();
                break;
            case '/api/validate':
                return (new Validation($db, $params))->run();
                break;
            default:
                throw new HttpException(404);
                break;
        }
    }
}
