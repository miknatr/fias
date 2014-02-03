<?php

namespace Fias\ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Handler
{
    public static function handleRequest($uri, $params, ConnectionInterface $db)
    {
        if (!preg_match('/^[^\?]+(|\/)/', $uri, $uri)) {
            throw new HttpException(404);
        }

        $uri = rtrim(array_shift($uri), '/');
        switch ($uri) {
            case '/api/complete':
                return static::complete($db, $params);
                break;
            case '/api/validate':
                return static::validate($db, $params);
                break;
            default:
                throw new HttpException(404);
                break;
        }
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function complete(ConnectionInterface $db, $params)
    {
        $address = !empty($params['address']) ? $params['address'] : null;
        $limit   = !empty($params['limit']) ? $params['limit'] : 50;
        $request = new Completion($db, $address, $limit);

        return $request->run();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function validate(ConnectionInterface $db, $params)
    {
        $address = !empty($params['address']) ? $params['address'] : null;
        $request = new Validation($db, $address);

        return $request->run();
    }
}
