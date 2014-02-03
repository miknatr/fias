<?php

namespace Fias\ApiAction;

use Fias\AddressStorage;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Validation implements ApiActionInterface
{
    /** @var ConnectionInterface */
    private $db;
    private $address;

    public function __construct(ConnectionInterface $db, array $params)
    {
        if (empty($params['address'])) {
            throw new HttpException(400);
        }

        $this->address = $params['address'];
        $this->db      = $db;
    }

    public function run()
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

        // Ничего не нашлось
        return array('is_complete' => false, 'is_valid' => false);
    }
}
