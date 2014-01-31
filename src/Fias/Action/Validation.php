<?php

namespace Fias\Action;

use Fias\AddressHelper;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class Validation implements Action
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
        $result = array(
            'is_complete' => false,
            'is_valid'    => false,
        );

        $completeAddress = AddressHelper::findHouse($this->db, $this->address);
        if ($completeAddress) {
            $result['is_complete'] = true;
            $result['is_valid']    = true;

            return $result;
        }

        $incompleteAddress = AddressHelper::findAddress($this->db, $this->address);
        if ($incompleteAddress) {
            $result['is_valid'] = true;

            return $result;
        }

        // Ничего не нашлось
        return $result;
    }
}
