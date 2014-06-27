<?php

namespace ApiAction;

use AddressStorage;
use BadRequestException;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressCompletion implements ApiActionInterface
{
    const BUILDING_ADDRESS_LEVEL = 0;

    /** @var ConnectionInterface */
    private $db;
    private $limit;
    private $address;
    private $parentId;
    private $maxAddressLevel;
    private $regions = array();

    public function __construct(ConnectionInterface $db, $address, $limit, $maxAddressLevel = 'building', array $regions = array())
    {
        $this->db      = $db;
        $this->limit   = $limit;
        $this->address = $address;
        $this->regions = $regions;

        if ($maxAddressLevel) {
            $this->maxAddressLevel = $this->getAddressLevelId($maxAddressLevel);
        }
    }

    public function run()
    {
        $storage      = new AddressStorage($this->db);
        $addressParts = static::splitAddress($this->address);

        $address        = $storage->findAddress($addressParts['address']);
        $this->parentId = $address ? $address['address_id'] : null;
        $houseCount     = $address ? $address['house_count'] : null;

        if ($houseCount && $this->maxAddressLevel) {
            return array();
        }

        if ($this->getHouseCount()) {
            $rows = $this->findHouses($addressParts['pattern']);
            $rows = $this->setIsCompleteFlag($rows);
        } else {
            $rows = $this->findAddresses($addressParts['pattern']);
            $rows = $this->setIsCompleteFlag($rows);
        }

        foreach ($rows as $key => $devNull) {
            $rows[$key]['tags'] = array('address');
        }

        return $rows;
    }

    private function getHouseCount()
    {
        if (!$this->parentId) {
            return null;
        }

        $sql = 'SELECT house_count FROM address_objects WHERE address_id = ?q';

        return $this->db->execute($sql, array($this->parentId))->fetchResult();
    }

    private function findAddresses($pattern)
    {
        $selectPart = "
            SELECT full_title title, address_level, next_address_level
            FROM address_objects ao
            WHERE ?p"
        ;

        $generalWhere = $this->generateGeneralWherePart($pattern);

        if ($this->parentId)
        {
            $values = array(
                $generalWhere . 'AND (' . $this->db->replacePlaceholders('parent_id = ?q', array($this->parentId)) . ')',
                $this->limit,
            );

            $sql = $this->db->replacePlaceholders($selectPart . ' ORDER BY ao.title LIMIT ?e', $values);
        } else {
            $values = array(
                $generalWhere . 'AND (parent_id IS NULL)',
            );

            $limitSql                = $this->db->replacePlaceholders('LIMIT ?e', array($this->limit));
            $subSelectWithoutParents = $this->db->replacePlaceholders($selectPart, $values);

            $values = array(
                $generalWhere,
                $this->limit,
            );

            $sqlWithRootParents = '
                SELECT ao.full_title title, ao.address_level, ao.next_address_level
                FROM address_objects ao
                INNER JOIN address_objects AS aop
                    ON aop.parent_id IS NULL
                        AND aop.address_id = ao.parent_id
                WHERE ?p' . $limitSql
            ;

            $subSelectWithRootParents = $this->db->replacePlaceholders($sqlWithRootParents, $values);

            $sql = '('
                . $subSelectWithoutParents
                . ') UNION ('
                . $subSelectWithRootParents
                . ') ORDER BY title ' . $limitSql;

        }

        return $this->db->execute($sql, array())->fetchAll();
    }

    private function generateGeneralWherePart($pattern)
    {
        $whereParts = array($this->db->replacePlaceholders("ao.title ilike '?e%'", array($pattern)));

        if ($this->maxAddressLevel) {
            $whereParts[] = $this->db->replacePlaceholders('ao.address_level <= ?q', array($this->maxAddressLevel));
        }

        if ($this->regions) {
            $whereParts[] = $this->db->replacePlaceholders('ao.region IN (?l)', array($this->regions));
        }

        return '(' . implode(') AND (', $whereParts) . ')';
    }

    private function findHouses($pattern)
    {
        $sql    = "
            SELECT full_title||', '||full_number title, ?q address_level, NULL next_address_level
            FROM houses h
            INNER JOIN address_objects ao
                ON ao.address_id = h.address_id
            WHERE h.address_id = ?q
                AND full_number ilike '?e%'
            ORDER BY (regexp_matches(full_number, '^[0-9]+', 'g'))[1]
            LIMIT ?e"
        ;
        $values = array(static::BUILDING_ADDRESS_LEVEL, $this->parentId, $pattern, $this->limit);

        return $this->db->execute($sql, $values)->fetchAll();
    }

    private function setIsCompleteFlag(array $values)
    {
        foreach ($values as $key => $value) {
            $isMaxLevelReached       = $value['address_level'] == $this->maxAddressLevel;
            $doChildrenSuitNextLevel = ($value['next_address_level'] <= $this->maxAddressLevel)
                || (!$this->maxAddressLevel && !empty($value['house_count']))
            ;
            $values[$key]['is_complete'] = $isMaxLevelReached || !$doChildrenSuitNextLevel;

            unset($values[$key]['address_level']);
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
