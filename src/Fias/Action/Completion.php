<?php

namespace Fias\Action;

use Fias\AddressHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Completion implements Action
{
    const MAX_LIMIT = 50;

    /** @var ConnectionInterface */
    private $db;
    private $address;
    private $parentId;
    private $limit;

    public function __construct(ConnectionInterface $db, $address, $limit)
    {
        $this->db      = $db;
        $this->address = $address;
        $this->limit   = (int)$limit;

        if ($this->limit > static::MAX_LIMIT) {
            $this->limit = static::MAX_LIMIT;
        }
    }

    public function run()
    {
        $addressParts   = $this->splitAddress();
        $this->parentId = AddressHelper::findAddress($this->db, $addressParts['address']);

        if ($this->getHousesCount()) {
            $rows = $this->findHouses($addressParts['pattern']);
        } else {
            $rows = $this->findAddresses($addressParts['pattern']);
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

    private function splitAddress()
    {
        $tmp = explode(',', $this->address);
        return array(
            'pattern' => trim(array_pop($tmp)),
            'address' => implode(',', $tmp),
        );
    }

    private function findAddresses($pattern)
    {
        $sql = "SELECT full_title
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

        return $this->db->execute($sql, $values)->fetchColumn();
    }

    private function findHouses($pattern)
    {
        $sql    = "SELECT full_title||', '||full_number
            FROM houses h
            INNER JOIN address_objects ao
                ON ao.address_id = h.address_id
            WHERE h.address_id = ?q
                AND full_number ilike  '?e%'
            ORDER BY (regexp_matches(full_number, '^[0-9]+', 'g'))[1]
            LIMIT ?e"
        ;
        $values = array($this->parentId, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchColumn();
    }
}
