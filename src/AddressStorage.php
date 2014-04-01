<?php

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
        $sql   = '
            SELECT address_id
            FROM address_objects
            WHERE level = ?q
                AND lower(full_title) = lower(?q)'
        ;

        return $this->db->execute($sql, array($level, $address))->fetchResult();
    }

    public function findHouse($address)
    {
        $tmp   = explode(',', $address);
        $house = trim(array_pop($tmp));

        $addressId = $this->findAddress(implode(',', $tmp));
        if ($addressId) {
            $sql = '
                SELECT house_id
                FROM houses
                WHERE address_id = ?q
                    AND full_number = lower(?q)'
            ;

            return $this->db->execute($sql, array($addressId, $house))->fetchResult();
        }

        return null;
    }
}
