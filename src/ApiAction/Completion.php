<?php

namespace ApiAction;

use AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Completion implements ApiActionInterface
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
        $this->limit   = $limit ?: static::MAX_LIMIT;
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

        $sql = 'SELECT house_count FROM address_objects WHERE address_id = ?q';

        return $this->db->execute($sql, array($this->parentId))->fetchResult();
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
            : '(
                parent_id IS NULL
                OR parent_id IN (
                    SELECT address_id
                    FROM address_objects
                    WHERE parent_id IS NULL
                )
            )'
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
            'pattern' => static::cleanAddressPart(array_pop($tmp)),
            'address' => implode(',', $tmp),
        );
    }

    private static function cleanAddressPart($rawAddress)
    {
        // избавляемся от популярных префиксов/постфиксов (Вопросы по поводу регулярки к johnnywoo, сам я ее слабо понимаю).
        $cleanAddress = preg_replace('
            {
                (?<= ^ | [^а-яА-ЯЁё] )

                (?:ул|улица|снт|деревня|тер|пер|переулок|ал|аллея|линия|проезд|гск|ш|шоссе|г|город|обл|область|пр|проспект)

                (?= [^а-яА-ЯЁё] | $ )

                [.,-]*
            }x',
            '',
            $rawAddress
        );


        return trim($cleanAddress);
    }
}
