<?php

namespace Fias\ApiAction;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Handler
{
    public static function handleRequest($uri, $params, ConnectionInterface $db)
    {
        $action = static::getAction($uri);
        switch($action) {
            case 'complete':
                return static::complete($db, $params);
                break;
            case 'validate':
                return static::validate($db, $params);
                break;
            default:
                throw new HttpException(404);
                break;
        }
    }

    private static function getAction($uri)
    {
        $tmp = explode('/', explode('?', $uri, 0)[0], 3);

        if ((count($tmp) < 2) || ($tmp[1] != 'api')) {
            throw new HttpException(400);
        }

        $action = $tmp[2];

        return $action;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function complete(ConnectionInterface $db, $params)
    {
        $address = !empty($params['address']) ? $params['address'] : null;
        $limit   = !empty($params['limit']) ? $params['address'] : 50;
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
