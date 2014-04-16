<?php

namespace ApiAction;

use AddressStorage;
use PlaceStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Validation implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $address;

    public function __construct(ConnectionInterface $db, $address)
    {
        $this->address = $address;
        $this->db      = $db;
    }

    public function run()
    {
        $addressData = $this->lookUpInFias();
        if ($addressData) {
            $addressData['object_type'] = 'address';
            return $addressData;
        }

        $placeData = $this->lookUpInPlaces();
        if ($placeData) {
            $placeData['object_type'] = 'place';
            return $placeData;
        }

        // ничего не нашлось
        return array('is_complete' => false, 'is_valid' => false, 'object_type' => null);
    }

    private function lookUpInFias()
    {
        $storage = new AddressStorage($this->db);

        $completeAddress = $storage->findHouse($this->address);
        if ($completeAddress) {
            return array('is_complete' => true, 'is_valid' => true);
        }

        $incompleteAddress = $storage->findAddress($this->address);
        if ($incompleteAddress) {
            return array('is_complete' => false, 'is_valid' => true);
        }

        // ничего не нашлось
        return null;
    }

    private function lookUpInPlaces()
    {
        $storage = new PlaceStorage($this->db);
        $place   = $storage->findPlace($this->address);

        if ($place) {
            return array('is_valid' => true, 'is_complete' => true);
        }

        return null;
    }
}
