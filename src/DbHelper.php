<?php

use FileSystem\FileHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class DbHelper
{
    private static $dataTypes = ['varchar', 'integer', 'uuid'];

    public static function createTable(ConnectionInterface $db, $name, $fields, $isTemp = true)
    {
        $sql    = '';
        $params = [$name];

        foreach ($fields as $field) {
            $params[] = $field['name'];
            $type     = !empty($field['type']) ? $field['type'] : 'varchar';

            if (!in_array($type, static::$dataTypes)) {
                throw new \LogicException('Некорректный тип: ' . $type);
            }

            $sql .= ', ?f ' . $type;
        }

        $sql = 'CREATE '
            . ($isTemp ? 'TEMP ' : '')
            . 'TABLE ?f ( '
            . substr($sql, 2)
            . ')'
        ;

        $db->execute($sql, $params);
    }

    public static function runFile($db, $path)
    {
        FileHelper::ensureIsReadable($path);
        $path = escapeshellarg($path);
        $db   = escapeshellarg($db);

        exec('psql -f ' . $path . ' ' . $db . ' 2>&1', $output, $result);

        if ($result !== 0) {
            throw new \Exception('Ошибка выполнения SQL файла: ' . implode("\n", $output));
        }

        if ($output) {
            foreach ($output as $line) {
                if (preg_match('/psql:(.*)ERROR:/', $line)) {
                    throw new \Exception('Ошибка выполнения SQL файла: ' . implode("\n", $output));
                }
            }
        }
    }
}
