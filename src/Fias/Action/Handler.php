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
        $tmp = explode('/', urldecode($uri));

        if (count($tmp) < 3) {
            throw new HttpException('Bad Request', 400);
        }

        $result = array('action' => $tmp[1]);
        if (!in_array($result['action'], static::$actions)) {
            throw new HttpException('Not Found', 404);
        }

        $result['address'] = $tmp[2];

        if ($result['action'] == 'complete') {
            $result['parent_id'] = !empty($tmp[3])
                ? $tmp[3]
                : null
            ;

            $result['limit'] = !empty($tmp[4])
                ? $tmp[4]
                : 50
            ;
        }

        return $result;
    }

    private static function complete(ConnectionInterface $db, $params)
    {
        $request = new Completion($db, $params['address'], $params['parent_id'], $params['limit']);

        return $request->run();
    }

    private static function validate(ConnectionInterface $db, $params)
    {
        $request = new Validation($db, $params['address']);

        return $request->run();
    }
}
