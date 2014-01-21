<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class DbHelper
{
    private static $dataTypes = array('varchar', 'integer', 'uuid');

    public static function createTable(ConnectionInterface $db, $name, $fields)
    {
        $sql    = '';
        $params = array($name);

        foreach ($fields as $field) {
            $params[] = $field['name'];
            $type     = !empty($field['type']) ? $field['type'] : 'varchar';

            if (!in_array($type, static::$dataTypes)) {
                throw new \LogicException('Некорректный тип: ' . $type);
            }

            $sql .= ', ?f ' . $type;
        }

        $sql = 'CREATE TABLE ?f ( ' . substr($sql, 2) . ')';

        $db->execute($sql, $params);
    }
}
