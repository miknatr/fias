<?php

namespace ApiAction;

use AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class PostalCodeToAddressCorrespondence implements ApiActionInterface
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
        $storage = new AddressStorage($this->db);
        return array('addresses' => $storage->findAddressByPostalCode($this->postCode));
    }
}
