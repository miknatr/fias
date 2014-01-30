<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Completion implements Action
{
    /** @var ConnectionInterface */
    private $db;
    private $address;
    private $parentId;
    private $limit;

    public function __construct(ConnectionInterface $db, $address, $parentId, $limit)
    {
        $this->db       = $db;
        $this->address  = $address;
        $this->parentId = $parentId;
        $this->limit    = (int)$limit;
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
        if (!$this->parentId) {
            return null;
        }

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
        $sql    = "SELECT address_id id, prefix||' '||title title, 0 is_complete
            FROM address_objects ao
            WHERE ?p
                AND title ilike  '?e%'
            ORDER BY ao.title
            LIMIT ?e"
        ;

        $parentPart = $this->parentId
            ? $this->db->replacePlaceholders('parent_id = ?q', array($this->parentId))
            : 'parent_id IS NULL'
        ;

        $values = array($parentPart, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }

    private function findHouses($pattern)
    {
        $sql    = "SELECT house_id id, full_number title, 1 is_complete
            FROM houses
            WHERE address_id = ?q
                AND full_number ilike  '?e%'
            ORDER BY (regexp_matches(full_number, '^[0-9]+', 'g'))[1]
            LIMIT ?e"
        ;
        $values = array($this->parentId, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }
}
