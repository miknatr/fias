<?php

namespace ApiAction;

use AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class AddressPostalCode implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $address;

    public function __construct(ConnectionInterface $db, $address)
    {
        $this->db      = $db;
        $this->address = $address;
    }

    public function run()
    {
        $storage = new AddressStorage($this->db);
        $address = $storage->findAddress($this->address);
        if ($address) {
            return $address['postal_code'];
        }

        $house = $storage->findHouse($this->address);
        if ($house) {
            if ($house['postal_code']) {
                return $house['postal_code'];
            }

            $address = $storage->findAddressById($house['address_id']);
            return $address['postal_code'];
        }

        // ничего не найдено
        return null;
    }
}
