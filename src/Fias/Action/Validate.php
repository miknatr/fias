<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Validate implements Action
{
    /** @var ConnectionInterface */
    private $db;
    private $address;

    public function __construct($address, $db)
    {
        $this->db      = $db;
        $this->address = $address;
    }

    public function run()
    {
        $address = $this->parseAddress();

        return $this->find($address);
    }

    private function parseAddress()
    {
        $tmp = explode(',', $this->address);

        return array(
            'house'   => trim(array_pop($tmp)),
            'address' => implode(',', $tmp),
            'level'   => count($tmp) - 1,
        );
    }

    private function find($address)
    {
        $result = array(
            'is_valid'    => false,
            'is_complete' => false,
            'id'          => null,
        );

        if ($address['level'] > 0) {
            $addressId = $this->findAddress($address['address'], $address['level']);

            if ($addressId) {
                $houseId = $this->findHouse($addressId, $address['house']);
                if ($houseId) {
                    $result['is_valid']    = true;
                    $result['is_complete'] = true;
                    $result['id']          = $houseId;

                    return $result;
                }
            }
        }

        $incompleteAddressId = $this->findAddress($this->address, $address['level'] + 1);
        if ($incompleteAddressId) {
            $result['is_valid'] = true;
            $result['id']       = $incompleteAddressId;

            return $result;
        }

        // Ничего не нашлось
        return $result;
    }

    private function findAddress($address, $level)
    {
        $sql = 'SELECT address_id
                FROM address_objects
                WHERE level = ?q
                    AND lower(full_title) = lower(?q)'
        ;

        $result = $this->db->execute($sql, array($level, $address))->fetchOneOrFalse();

        return $result ? $result['address_id'] : false;
    }

    private function findHouse($addressId, $house)
    {
        $sql = 'SELECT home_id
                FROM houses
                WHERE address_id = ?q
                    AND full_number = lower(?q)'
        ;

        $result = $this->db->execute($sql, array($addressId, $house))->fetchOneOrFalse();

        return $result ? $result['home_id'] : false;
    }
}
