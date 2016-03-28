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
            SELECT *
            FROM address_objects
            WHERE level = ?q
                AND lower(full_title) = lower(?q)
            LIMIT 1'
        ;

        return $this->db->execute($sql, [$level, $address])->fetchOneOrFalse();
    }

    public function findHouse($address)
    {
        $tmp   = explode(',', $address);
        $house = trim(array_pop($tmp));

        $address = $this->findAddress(implode(',', $tmp));
        if ($address) {
            $addressId = $address['address_id'];
            $sql       = '
                SELECT *
                FROM houses
                WHERE address_id = ?q
                    AND full_number = lower(?q)
                LIMIT 1'
            ;

            return $this->db->execute($sql, [$addressId, $house])->fetchOneOrFalse();
        }

        return false;
    }

    public function findAddressById($id)
    {
        $sql = '
            SELECT *
            FROM address_objects
            WHERE address_id = ?q
            ORDER BY level DESC
            LIMIT 1'
        ;

        return $this->db->execute($sql, [$id])->fetchOneOrFalse();
    }

    public function findHousesByPostalCode($postalCode)
    {
        $sql = '
            SELECT *
            FROM houses
            WHERE postal_code = ?q'
        ;

        return $this->db->execute($sql, [$postalCode])->fetchResult();
    }
}
