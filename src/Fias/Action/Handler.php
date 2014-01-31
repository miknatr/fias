<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Handler
{
    private static $actions = array('complete', 'validate');

    public static function handle($uri, ConnectionInterface $db)
    {
        $params = static::parseUri($uri);

        return static::$params['action']($db, $params);
    }

    private static function parseUri($uri)
    {
        $tmp = explode('/', explode('?', $uri)[0]);

        if ((count($tmp) < 2) || ($tmp[1] != 'api')) {
            throw new HttpException('Bad Request', 400);
        }

        $result = array('action' => $tmp[2]);
        if (!in_array($result['action'], static::$actions)) {
            throw new HttpException('Not Found', 404);
        }

        if ($result['action'] == 'complete') {
            $result['limit'] = static::getParam('limit', 50);
        }

        $result['address'] = static::getParam('address');

        return $result;
    }

    private static function getParam($name, $default = null)
    {
        return !empty($_GET[$name])
            ? $_GET[$name]
            : $default
        ;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function complete(ConnectionInterface $db, $params)
    {
        $request = new Completion($db, $params['address'], $params['limit']);

        return $request->run();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function validate(ConnectionInterface $db, $params)
    {
        $request = new Validation($db, $params['address']);

        return $request->run();
    }
}
