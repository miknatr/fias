<?php

namespace ApiAction;

use AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PostalCodeToAddressCorrespondence implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $postalCode;

    public function __construct(ConnectionInterface $db, $postalCode)
    {
        $this->db       = $db;
        $this->postalCode = $postalCode;
    }

    public function run()
    {
        $storage = new AddressStorage($this->db);
        return array('addresses' => $storage->findAddressByPostalCode($this->postalCode));
    }
}
