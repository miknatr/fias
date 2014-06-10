<?php

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PlaceStorage
{
    /** @var ConnectionInterface */
    private $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function findPlace($place)
    {
        $sql = '
            SELECT p.id, tp.system_name as type_system_name
            FROM places p
            INNER JOIN place_types tp
                ON tp.id = p.type_id
            WHERE lower(full_title) = lower(?q)'
        ;

        return $this->db->execute($sql, array($place))->fetchOneOrFalse();
    }
}
