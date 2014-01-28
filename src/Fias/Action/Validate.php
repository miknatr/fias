<?php

namespace Fias\Action;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Validate
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
        foreach ($tmp as $key => $part) {
            $tmp[$key] = rtrim($part);
        }

        return array(
            'address' => implode(', ', $tmp),
            'house'   => array_pop($tmp),
        );
    }

    private function find($address)
    {
        $result = array(
            'is_valid'   => false,
            'incomplete' => true,
            'id'         => null,
        );

        $addressId = $this->findAddress($address['address']);

        if ($addressId) {
            $houseId = $this->findHouse($addressId, $address['house']);
            if ($houseId) {
                $result['is_valid']    = true;
                $result['is_complete'] = true;
                $result['id']          = $houseId;
            }
        } else {
            $incompleteAddressId = $this->findAddress($this->address);
            if ($incompleteAddressId) {
                $result['is_valid'] = true;
                $result['id']       = $incompleteAddressId;
                return $result;
            }
        }


        // Ничего не нашлось
        return $result;
    }

    private function findAddress($address)
    {
        return null;
    }

    private function findHouse($parentId, $house)
    {
        return null;
    }
}
