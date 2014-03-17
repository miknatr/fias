<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class RawDataHelper
{
    public static function cleanAddressObjects(ConnectionInterface $db, $table = 'address_objects')
    {
        // Формируем полный заголовок
        $sql = <<<SQL
            UPDATE ?f ao SET
                level      = tmp.level,
                full_title = tmp.title
            FROM (
                WITH RECURSIVE required_addresses(level, address_id, title) AS (
                    SELECT DISTINCT 0, address_id, "prefix" || ' ' || title
                    FROM address_objects
                    WHERE parent_id IS NULL
                UNION ALL
                    SELECT ra.level + 1, ar.address_id, ra.title || ', ' || "prefix" || ' ' || ar.title
                    FROM address_objects ar
                    INNER JOIN required_addresses ra
                        ON ra.address_id = ar.parent_id
                )
                SELECT * FROM required_addresses
            ) tmp
            WHERE tmp.address_id = ao.address_id;
SQL;

        $db->execute($sql, array($table));
    }
}
