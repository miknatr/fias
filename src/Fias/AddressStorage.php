<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressStorage
{
    /** @var ConnectionInterface */
    private $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function findAddress($address)
    {
        $level = count(explode(',', $address)) - 1;
        $sql   = 'SELECT address_id
                  FROM address_objects
                  WHERE level = ?q
                      AND lower(full_title) = lower(?q)'
        ;

        $result = $this->db->execute($sql, array($level, $address))->fetchOneOrFalse();

        return $result ? $result['address_id'] : null;
    }

    public function findHouse($address)
    {
        $tmp   = explode(',', $address);
        $house = trim(array_pop($tmp));

        $addressId = $this->findAddress(implode(',', $tmp));
        if ($addressId) {
            $sql = 'SELECT house_id
                    FROM houses
                    WHERE address_id = ?q
                        AND full_number = lower(?q)'
            ;

            $result = $this->db->execute($sql, array($addressId, $house))->fetchOneOrFalse();
            if ($result) {
                return $result['house_id'];
            }
        }

        return null;
    }
}
