<?php

namespace Fias\ApiAction;

use Fias\AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Completion implements ApiActionInterface
{
    const MAX_LIMIT = 50;

    /** @var ConnectionInterface */
    private $db;
    private $address;
    private $parentId;
    private $limit;

    public function __construct(ConnectionInterface $db, array $params)
    {

        $this->db      = $db;
        $this->address = !empty($params['address']) ? $params['address'] : null;
        $this->limit   = !empty($params['limit']) ? (int) $params['limit'] : 50;

        if ($this->limit > static::MAX_LIMIT) {
            throw new HttpException(400);
        }
    }

    public function run()
    {
        $storage        = new AddressStorage($this->db);
        $addressParts   = static::splitAddress($this->address);
        $this->parentId = $storage->findAddress($addressParts['address']);

        if ($this->getHousesCount()) {
            $rows = $this->findHouses($addressParts['pattern']);
        } else {
            $rows = $this->findAddresses($addressParts['pattern']);
        }

        return array('addresses' => $rows);
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

    private function findAddresses($pattern)
    {
        $sql = "
            SELECT full_title title, 0 is_complete
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
        $sql    = "
            SELECT full_title||', '||full_number title, 1 is_complete
            FROM houses h
            INNER JOIN address_objects ao
                ON ao.address_id = h.address_id
            WHERE h.address_id = ?q
                AND full_number ilike '?e%'
            ORDER BY (regexp_matches(full_number, '^[0-9]+', 'g'))[1]
            LIMIT ?e"
        ;
        $values = array($this->parentId, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }

    private static function splitAddress($address)
    {
        $tmp = explode(',', $address);

        return array(
            'pattern' => trim(array_pop($tmp)),
            'address' => implode(',', $tmp),
        );
    }
}
