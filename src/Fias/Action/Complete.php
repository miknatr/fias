<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Complete implements Action
{
    /** @var ConnectionInterface */
    private $db;
    private $address;
    private $parentId;

    public function __construct($address, $parentId, $db)
    {
        $this->db       = $db;
        $this->address  = $address;
        $this->parentId = $parentId;
    }

    public function run()
    {
        $pattern = $this->getPartForCompletion();

        if ($this->getHousesCount()) {
            $rows = $this->findHouses($pattern);
        } else {
            $rows = $this->findAddresses($pattern);
        }

        return array(
            'count' => count($rows),
            'rows'  => $rows
        );
    }

    private function getHousesCount()
    {
        $sql    = 'SELECT house_count FROM address_objects WHERE address_id = ?q';
        $result = $this->db->execute($sql, array($this->parentId))->fetchOneOrFalse();

        return $result ? $result['house_count'] : null;
    }

    private function getPartForCompletion()
    {
        $tmp = explode(',', $this->address);

        return trim(array_pop($tmp));
    }

    private function findAddresses($pattern)
    {
        $sql = "SELECT address_id as id, prefix||' '||title as title
            FROM address_objects ao
            WHERE parent_id = ?q
                AND title ilike  '?e%'
            ORDER BY ao.title"
        ;

        return $this->db->execute($sql, array($this->parentId, $pattern))->fetchAll();
    }

    private function findHouses($pattern)
    {
        $sql = "SELECT home_id as id, full_number title, TRUE as is_complete
            FROM houses
            WHERE address_id = ?q
                AND full_number ilike  '?e%'
            ORDER BY (regexp_matches(full_number, '^[0-9]+', 'g'))[1]"
        ;

        return $this->db->execute($sql, array($this->parentId, $pattern))->fetchAll();
    }
}
