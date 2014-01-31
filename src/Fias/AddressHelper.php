<?php

namespace Fias;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressHelper
{
    public static function findAddress(ConnectionInterface $db, $address)
    {
        $level = count(explode(',', $address)) - 1;
        $sql   = 'SELECT address_id
                  FROM address_objects
                  WHERE level = ?q
                      AND lower(full_title) = lower(?q)'
        ;

        $result = $db->execute($sql, array($level, $address))->fetchOneOrFalse();

        return $result ? $result['address_id'] : null;
    }

    public static function findHouse(ConnectionInterface $db, $address)
    {
        $tmp   = explode(',', $address);
        $house = trim(array_pop($tmp));

        $addressId = static::findAddress($db, implode(',', $tmp));
        if ($addressId) {
            $sql = 'SELECT house_id
                    FROM houses
                    WHERE address_id = ?q
                        AND full_number = lower(?q)'
            ;

            $result = $db->execute($sql, array($addressId, $house))->fetchOneOrFalse();
            if ($result) {
                return $result['house_id'];
            }
        }

        return null;
    }
}
