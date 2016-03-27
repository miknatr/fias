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
        $result = [];

        $addressData = $this->lookUpInFias();
        if ($addressData) {
            $addressData['tags'] = ['address'];
            $result[]            = $addressData;
        }

        $placeData = $this->lookUpInPlaces();
        if ($placeData) {
            $result[] = [
                'is_valid'    => $placeData['is_valid'],
                'is_complete' => $placeData['is_complete'],
                'tags'        => ['place', $placeData['type_system_name']]
            ];
        }

        return $result;
    }

    private function lookUpInFias()
    {
        $storage = new AddressStorage($this->db);

        $completeAddress = $storage->findHouse($this->address);
        if ($completeAddress) {
            return ['is_complete' => true, 'is_valid' => true];
        }

        $incompleteAddress = $storage->findAddress($this->address);
        if ($incompleteAddress) {
            return ['is_complete' => false, 'is_valid' => true];
        }

        // ничего не нашлось
        return null;
    }

    private function lookUpInPlaces()
    {
        $storage = new PlaceStorage($this->db);
        $place   = $storage->findPlace($this->address);

        if ($place) {
            return [
                'type_system_name' => $place['type_system_name'],
                'is_valid'         => true,
                'is_complete'      => true
            ];
        }

        return null;
    }
}
