<?php

namespace ApiAction;

use AddressStorage;
use BadRequestException;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressCompletion implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $limit;
    private $address;
    private $parentId;
    private $maxDepth;
    private $addressLevels = array();
    private $regions = array();

    public function __construct(ConnectionInterface $db, $address, $limit, $maxDepth = 'building', array $addressLevels = array(), array $regions = array())
    {
        $this->db      = $db;
        $this->limit   = $limit;
        $this->address = $address;
        $this->regions = $regions;

        if ($maxDepth) {
            $this->maxDepth = $this->getAddressLevelId($maxDepth);
        }

        if ($addressLevels) {
            foreach ($addressLevels as $level) {
                $this->addressLevels[] = $this->getAddressLevelId($level);
            }
        }
    }

    public function run()
    {
        $storage      = new AddressStorage($this->db);
        $addressParts = static::splitAddress($this->address);

        $address        = $storage->findAddress($addressParts['address']);
        $this->parentId = $address ? $address['address_id'] : null;
        $housesCount    = $address ? $address['house_count'] : null;

        if ($housesCount && ($this->maxDepth || $this->addressLevels)) {
            return array();
        }

        if ($this->getHousesCount()) {
            $rows = $this->findHouses($addressParts['pattern']);
            $rows = $this->setIsCompleteFlag($rows, true);
        } else {
            $rows = $this->findAddresses($addressParts['pattern']);
            $rows = $this->setIsCompleteFlag($rows, false);
        }

        return $rows;
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
            SELECT full_title title
            FROM address_objects ao
            WHERE ?p
            ORDER BY ao.title
            LIMIT ?e"
        ;

        $whereParts = array($this->db->replacePlaceholders("title ilike '?e%'", array($pattern)));

        if ($this->maxDepth) {
            $whereParts[] = $this->db->replacePlaceholders('address_level <= ?q', array($this->maxDepth));
        }

        if ($this->addressLevels) {
            $whereParts[] = $this->db->replacePlaceholders('address_level IN (?l)', array($this->addressLevels));
        }

        if ($this->regions) {
            $whereParts[] = $this->db->replacePlaceholders('region IN (?l)', array($this->regions));
        }

        $whereParts[] = $this->parentId
            ? $this->db->replacePlaceholders('parent_id = ?q', array($this->parentId))
            : '
                parent_id IS NULL
                OR parent_id IN (
                  SELECT address_id
                  FROM address_objects
                  WHERE parent_id IS NULL
                )
            '
        ;

        $values = array('(' . implode(') AND (', $whereParts) . ')', $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }

    private function findHouses($pattern)
    {
        $sql    = "
            SELECT full_title||', '||full_number title
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

    private function setIsCompleteFlag(array $values, $flag)
    {
        // Проставляем здесь, а не в запросе так как хотим получить в JSON честное false а не 0
        foreach ($values as $key => $value) {
            $values[$key]['is_complete'] = $flag;
        }

        return $values;
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

    public function getAddressLevelId($code)
    {
        $result = $this->db->execute(
            'SELECT id FROM address_object_levels WHERE code = ?q',
            array($code)
        )->fetchResult();

        if ($result === null) {
            throw new BadRequestException('Некорректное значение уровня адреса: ' . $code);
        }

        return $result;
    }
}
