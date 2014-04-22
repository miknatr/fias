<?php

namespace ApiAction;

use AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PostCodeToAddressCorrespondence implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $postCode;

    public function __construct(ConnectionInterface $db, $postCode)
    {
        $this->db       = $db;
        $this->postCode = $postCode;
    }

    public function run()
    {
        $result  = array('address' => null, 'houses' => null);
        $storage = new AddressStorage($this->db);
        $addresses = $storage->findAddressByPostalCode($this->postCode);

        if ($addresses) {
            $result['addresses'] = $addresses;
            $result['houses']    = $storage->findHousesByPostalCode($this->postCode);
        }

        return $result;
    }
}
