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

    public static function cleanHouses(ConnectionInterface $db, $table = 'houses')
    {
        $inCorrectValues = array('нет', '-', 'стр.', 'стр1');

        // Если будем импортировать больше половины регионов из фиаса, перенести на сторону PHP.
        $db->execute(
            "UPDATE ?f SET
                number    = lower(number),
                building  = CASE WHEN building  IN (?l) THEN NULL ELSE lower(building)  END,
                structure = CASE WHEN structure IN (?l) THEN NULL ELSE lower(structure) END
            WHERE number ~ '[^0-9]+'
                OR building  ~ '[^0-9]+'
                OR structure ~ '[^0-9]+'
            ",
            array($table, $inCorrectValues, $inCorrectValues)
        );

        // Убираем ложные данные по корпусам и строениям ("1а" и в корпусе и в номере, например)
        $db->execute(
            "UPDATE ?f SET
                building = NULL,
                structure = NULL
            WHERE number ~ '[^0-9]+'
                AND (
                    (structure ~ '[^0-9]+' AND number = structure)
                    OR
                    (building ~ '[^0-9]+' AND number = building)
                )
            ",
            array($table)
        );

        // нормализуем адрес по яндексу
        $db->execute(
            "UPDATE ?f
                SET full_number = COALESCE(number, '')
                    ||COALESCE('к'||building, '')
                    ||COALESCE('с'||structure, '')
            ",
            array($table)
        );
    }

    public static function updateHousesCount(ConnectionInterface $db, $table = 'houses')
    {
        // прописываем данные по домам в address_objects
        $db->execute(
            "UPDATE address_objects ao
                SET house_count = tmp.count
            FROM (
                SELECT address_id, count(*) count
                FROM houses GROUP BY 1
            ) tmp
            WHERE tmp.address_id = ao.address_id
            ",
            array($table)
        );
    }
}
