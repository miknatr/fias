<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Handler
{
    private static $actions = array('complete', 'validate');

    public static function handle($uri, ConnectionInterface $db)
    {
        $params = static::parseUri($uri);

        return static::$params['action']($params, $db);
    }

    private static function parseUri($uri)
    {
        $tmp = explode('/', $uri);

        if (count($tmp) < 3) {
            throw new Exception('Bad Request', 400);
        }

        $result = array('action' => $tmp[1]);
        if (!in_array($result['action'], static::$actions)) {
            throw new Exception('Not Found', 404);
        }

        $result['address'] = $tmp[2];

        if ($result['action'] == 'complete') {
            if (!empty($tmp[3])) {
                $result['parent_id'] = $tmp[3];
            }

            if (!empty($tmp[4])) {
                $result['limit'] = $tmp[4];
            }
        }

        return $result;
    }

    private static function complete($params, ConnectionInterface $db)
    {

    }

    private static function validate($params, ConnectionInterface $db)
    {
        $request = new Validate($params['address'], $db);
        return $request->run();
    }
}
