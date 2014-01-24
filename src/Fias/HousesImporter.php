<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class HousesImporter extends Importer
{

    public function __construct(ConnectionInterface $db, $table, array $fields)
    {
        parent::__construct($db, $table, $fields, false);
    }

    public function modifyDataAfterImport()
    {
        // Чистим левые записи из левых регинов. БЫСТРЕЕ чем NOT IN и DELETE .. USING
        $this->db->execute(
            'DELETE FROM ?f h
                WHERE NOT EXISTS (
                    SELECT address_id
                    FROM address_objects as ao
                    WHERE ao.address_id = h.address_id
                )',
            array($this->table)
        );

        // STOPPER работает паршиво перенести на фильтры при загрузке в БД
        $this->db->execute(
            "UPDATE ?f SET
                number    = lower(number),
                building  = CASE WHEN building  IN ('нет', '-') THEN NULL ELSE lower(building)  END,
                structure = CASE WHEN structure IN ('нет', '-') THEN NULL ELSE lower(structure) END
            WHERE number ~ '[^0-9]+'
                OR building  ~ '[^0-9]+'
                OR structure ~ '[^0-9]+'
            ",
            array($this->table)
        );

        // Убираем ложные данные по корпусам и строениям ("1а" и в корпусе и в номере, например)
        $this->db->execute(
            "UPDATE ?f SET
                building = NULL,
                structure = NULL
            WHERE number ~ '[^0-9]+'
                AND (
                    (structure ~ '[^0-9]+' AND number = structure)
                    OR
                    (building ~ '[^0-9]+' AND number = building)
                )",
            array($this->table)
        );
    }
}
