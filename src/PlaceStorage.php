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
            SELECT id
            FROM places
            WHERE lower(full_title) = lower(?q)'
        ;

        return $this->db->execute($sql, array($place))->fetchResult();
    }
}
